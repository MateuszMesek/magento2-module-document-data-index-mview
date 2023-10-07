<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\ObjectManagerInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\ChangelogTableNameResolverInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\SubscriptionProviderInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\TriggerNameResolverInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\TriggerProviderInterface;
use Traversable;

class TriggerProvider implements TriggerProviderInterface
{
    public function __construct(
        private readonly SubscriptionProviderInterface       $subscriptionProvider,
        private readonly ChangelogTableNameResolverInterface $changelogTableNameResolver,
        private readonly TriggerNameResolverInterface        $triggerNameResolver,
        private readonly TriggerFactory                      $triggerFactory,
        private readonly ObjectManagerInterface              $objectManager,
        private readonly ResourceConnection                  $resourceConnection
    )
    {
    }

    public function get(array $context): Traversable
    {
        $subscriptionsByPath = $this->subscriptionProvider->get($context);
        $changelogTableName = $this->changelogTableNameResolver->resolve($context);

        $connection = $this->resourceConnection->getConnection();

        $triggersByName = [];
        $statementsByTriggerName = [];

        foreach ($subscriptionsByPath as $path => $subscriptions) {
            foreach ($subscriptions as $subscription) {
                ['type' => $type, 'arguments' => $arguments] = $subscription;

                $generator = $this->objectManager->get($type);

                /** @var \MateuszMesek\DocumentDataIndexMviewApi\Model\Data\SubscriptionInterface[] $subscriptionItems */
                $subscriptionItems = call_user_func_array([$generator, 'generate'], $arguments);

                foreach ($subscriptionItems as $subscriptionItem) {
                    $triggerName = $this->triggerNameResolver->resolver($context, $subscriptionItem);

                    if (!isset($triggersByName[$triggerName])) {
                        $triggersByName[$triggerName] = ($this->triggerFactory->create())
                            ->setName($triggerName)
                            ->setTable($this->resourceConnection->getTableName($subscriptionItem->getTableName()))
                            ->setTime($subscriptionItem->getTriggerTime())
                            ->setEvent($subscriptionItem->getTriggerEvent());
                    }

                    if (!isset($statementsByTriggerName[$triggerName])) {
                        $statementsByTriggerName[$triggerName] = [];
                    }

                    $condition = (string)$subscriptionItem->getCondition();

                    if (!isset($statementsByTriggerName[$triggerName][$condition])) {
                        $statementsByTriggerName[$triggerName][$condition] = [];
                    }

                    $statement = sprintf(
                        <<<SQL
                        SET @documentId = %1\$s;
                        SET @nodePath = %2\$s;
                        SET @dimensions = %3\$s;
                        INSERT INTO %4\$s (`document_id`, `node_path`, `dimensions`)
                        SELECT IFNULL(t.document_id, @documentId),
                               IFNULL(t.node_path, @nodePath),
                               CONVERT(IFNULL(t.dimensions, @dimensions) USING UTF8MB4)
                        FROM (
                        %5\$s
                        ) AS t
                        WHERE IFNULL(t.document_id, @documentId) IS NOT NULL
                        ON DUPLICATE KEY UPDATE
                            `changed_at` = NOW();
                        SQL,
                        $subscriptionItem->getDocumentId() ?? 'NULL',
                        $connection->quote($path),
                        $subscriptionItem->getDimensions() ?? $connection->quote('{}'),
                        $connection->quoteIdentifier($changelogTableName),
                        $subscriptionItem->getRows() ?? 'SELECT NULL AS `document_id`, NULL AS `node_path`, NULL AS `dimensions`'
                    );

                    $statementsByTriggerName[$triggerName][$condition][] = $statement;
                }
            }
        }

        foreach ($triggersByName as $triggerName => $trigger) {
            $statements = [];

            foreach ($statementsByTriggerName[$triggerName] as $condition => $conditionStatements) {
                $conditionStatement = implode("\n\n", $conditionStatements);

                if ($condition !== '') {
                    $conditionStatement = <<<SQL
                    IF $condition THEN
                    $conditionStatement
                    END IF;
                    SQL;
                }

                $statements[] = $conditionStatement;
            }

            $statement = implode("\n", $statements);

            $statement = $this->addUpdateCondition($trigger, $statement);

            $trigger->addStatement($statement);
        }

        yield from array_values(
            $triggersByName
        );
    }

    private function addUpdateCondition(Trigger $trigger, string $statement): string
    {
        if ($trigger->getEvent() !== Trigger::EVENT_UPDATE) {
            return $statement;
        }

        $columns = $this->getTableColumns($trigger->getTable());

        if (empty($columns)) {
            return $statement;
        }

        $conditions = [];

        foreach ($columns as $column) {
            if ($column === 'updated_at') {
                continue;
            }

            $conditions[] = <<<SQL
            NOT(NEW.$column <=> OLD.$column)
            SQL;
        }

        $condition = implode(' OR ', $conditions);

        return <<<SQL
        IF $condition THEN
        $statement
        END IF;
        SQL;
    }

    private function getTableColumns(string $tableName): array
    {
        $connection = $this->resourceConnection->getConnection();

        if (!$connection->isTableExists($tableName)) {
            return [];
        }

        return array_map(
            static function ($columnData) {
                return $columnData['COLUMN_NAME'];
            },
            array_values(
                $connection->describeTable($tableName)
            )
        );
    }
}
