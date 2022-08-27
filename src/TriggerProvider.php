<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\DB\Ddl\TriggerFactory;
use Magento\Framework\ObjectManagerInterface;
use MateuszMesek\DocumentDataIndexMviewApi\ChangelogTableNameResolverInterface;
use MateuszMesek\DocumentDataIndexMviewApi\SubscriptionProviderInterface;
use MateuszMesek\DocumentDataIndexMviewApi\TriggerNameResolverInterface;
use MateuszMesek\DocumentDataIndexMviewApi\TriggerProviderInterface;
use Traversable;

class TriggerProvider implements TriggerProviderInterface
{
    private SubscriptionProviderInterface $subscriptionProvider;
    private ChangelogTableNameResolverInterface $changelogTableNameResolver;
    private TriggerNameResolverInterface $triggerNameResolver;
    private TriggerFactory $triggerFactory;
    private ObjectManagerInterface $objectManager;
    private ResourceConnection $resourceConnection;

    public function __construct(
        SubscriptionProviderInterface       $subscriptionProvider,
        ChangelogTableNameResolverInterface $changelogTableNameResolver,
        TriggerNameResolverInterface        $triggerNameResolver,
        TriggerFactory                      $triggerFactory,
        ObjectManagerInterface              $objectManager,
        ResourceConnection                  $resourceConnection
    )
    {
        $this->subscriptionProvider = $subscriptionProvider;
        $this->changelogTableNameResolver = $changelogTableNameResolver;
        $this->triggerNameResolver = $triggerNameResolver;
        $this->triggerFactory = $triggerFactory;
        $this->objectManager = $objectManager;
        $this->resourceConnection = $resourceConnection;
    }

    public function get(array $context): Traversable
    {
        $subscriptionsByPath = $this->subscriptionProvider->get($context);
        $changelogTableName = $this->changelogTableNameResolver->resolve($context);

        $connection = $this->resourceConnection->getConnection();

        $triggers = [];

        foreach ($subscriptionsByPath as $path => $subscriptions) {
            foreach ($subscriptions as $subscription) {
                ['type' => $type, 'arguments' => $arguments] = $subscription;

                $generator = $this->objectManager->get($type);

                /** @var \MateuszMesek\DocumentDataIndexMviewApi\Data\SubscriptionInterface[] $subscriptionItems */
                $subscriptionItems = call_user_func_array([$generator, 'generate'], $arguments);

                foreach ($subscriptionItems as $subscriptionItem) {
                    $triggerName = $this->triggerNameResolver->resolver($context, $subscriptionItem);

                    if (!isset($triggers[$triggerName])) {
                        $triggers[$triggerName] = ($this->triggerFactory->create())
                            ->setName($triggerName)
                            ->setTable($this->resourceConnection->getTableName($subscriptionItem->getTableName()))
                            ->setTime($subscriptionItem->getTriggerTime())
                            ->setEvent($subscriptionItem->getTriggerEvent());
                    }

                    $statement = sprintf(
                        <<<SQL
                            SET @documentId = %1\$s;
                            SET @nodePath = %2\$s;
                            SET @dimensions = %3\$s;
                            INSERT INTO %4\$s (`document_id`, `node_path`, `dimensions`)
                            SELECT IFNULL(t.document_id, @documentId), IFNULL(t.node_path, @nodePath), CONVERT(IFNULL(t.dimensions, @dimensions) USING UTF8MB4)
                            FROM (%5\$s) AS t
                            WHERE IFNULL(t.document_id, @documentId) IS NOT NULL
                            ON DUPLICATE KEY UPDATE
                                `document_id` = VALUES(`document_id`),
                                `node_path` = VALUES(`node_path`),
                                `dimensions` = VALUES(`dimensions`);
                        SQL,
                        $subscriptionItem->getDocumentId() ?? 'NULL',
                        $connection->quote($path),
                        $subscriptionItem->getDimensions() ?? $connection->quote('{}'),
                        $connection->quoteIdentifier($changelogTableName),
                        $subscriptionItem->getRows() ?? 'SELECT NULL AS `document_id`, NULL AS `node_path`, NULL AS `dimensions`'
                    );

                    if ($condition = $subscriptionItem->getCondition()) {
                        $statement = <<<SQL
                            IF $condition THEN $statement END IF;
                        SQL;
                    }

                    if ($triggers[$triggerName]->getEvent() === Trigger::EVENT_UPDATE) {
                        $columns = $connection->describeTable($triggers[$triggerName]->getTable());

                        $conditions = [];

                        foreach ($columns as $columnData) {
                            ['COLUMN_NAME' => $columnName] = $columnData;

                            if ($columnName === 'updated_at') {
                                continue;
                            }

                            $conditions[] = <<<SQL
                                (NEW.$columnName != OLD.$columnName)
                            SQL;
                        }

                        $condition = implode(' OR ', $conditions);

                        $statement = <<<SQL
                            IF $condition THEN $statement END IF;
                        SQL;
                    }

                    $triggers[$triggerName]->addStatement($statement);
                }
            }
        }

        yield from $triggers;
    }
}
