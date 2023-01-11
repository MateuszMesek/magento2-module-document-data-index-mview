<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Plugin\UpdateDbTriggers;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mview\View\SubscriptionInterface;
use Magento\Framework\Mview\ViewInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\ChangelogTableNameResolverInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\DocumentNameResolverInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\TriggerProviderInterface;

class OnSubscription
{
    public function __construct(
        private readonly DocumentNameResolverInterface       $documentNameResolver,
        private readonly TriggerProviderInterface            $triggerProvider,
        private readonly ChangelogTableNameResolverInterface $changelogTableNameResolver,
        private readonly ResourceConnection                  $resource
    )
    {
    }

    public function beforeCreate(
        SubscriptionInterface $subscription
    ): void
    {
        $view = $subscription->getView();

        if (!$view instanceof ViewInterface) {
            return;
        }

        $context = [
            'view_id' => $view->getId()
        ];

        $documentName = $this->documentNameResolver->resolver($context);

        if (null === $documentName) {
            return;
        }

        $context['document_name'] = $documentName;

        $triggers = iterator_to_array(
            $this->triggerProvider->get($context)
        );

        $changelogTableName = $this->changelogTableNameResolver->resolve($context);

        $connection = $this->resource->getConnection();
        $connection->query(sprintf(
            <<<SQL
                CREATE TABLE IF NOT EXISTS %s LIKE %s
            SQL,
            $this->resource->getTableName($changelogTableName),
            $this->resource->getTableName('document_data_mview_pattern')
        ));

        foreach ($triggers as $trigger) {
            $connection->dropTrigger($trigger->getName());

            if (!$connection->isTableExists($trigger->getTable())) {
                continue;
            }

            $connection->createTrigger($trigger);
        }
    }

    public function afterRemove(
        SubscriptionInterface $subscription
    ): void
    {
        $view = $subscription->getView();

        if (!$view instanceof ViewInterface) {
            return;
        }

        $context = [
            'view_id' => $view->getId()
        ];

        $documentName = $this->documentNameResolver->resolver($context);

        if (null === $documentName) {
            return;
        }

        $context['document_name'] = $documentName;

        $triggers = iterator_to_array(
            $this->triggerProvider->get($context)
        );

        $connection = $this->resource->getConnection();

        foreach ($triggers as $trigger) {
            $connection->dropTrigger($trigger->getName());
        }

        $changelogTableName = $this->changelogTableNameResolver->resolve($context);

        $connection->dropTable(
            $this->resource->getTableName($changelogTableName)
        );
    }
}
