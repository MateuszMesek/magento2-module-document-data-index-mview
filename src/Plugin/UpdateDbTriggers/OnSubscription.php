<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Plugin\UpdateDbTriggers;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mview\View\SubscriptionInterface;
use Magento\Framework\Mview\ViewInterface;
use MateuszMesek\DocumentDataIndexMviewApi\ChangelogTableNameResolverInterface;
use MateuszMesek\DocumentDataIndexMviewApi\DocumentNameResolverInterface;
use MateuszMesek\DocumentDataIndexMviewApi\TriggerProviderInterface;

class OnSubscription
{
    private DocumentNameResolverInterface $documentNameResolver;
    private TriggerProviderInterface $triggerProvider;
    private ChangelogTableNameResolverInterface $changelogTableNameResolver;
    private ResourceConnection $resource;

    public function __construct(
        DocumentNameResolverInterface       $documentNameResolver,
        TriggerProviderInterface            $triggerProvider,
        ChangelogTableNameResolverInterface $changelogTableNameResolver,
        ResourceConnection                  $resource
    )
    {
        $this->documentNameResolver = $documentNameResolver;
        $this->triggerProvider = $triggerProvider;
        $this->changelogTableNameResolver = $changelogTableNameResolver;
        $this->resource = $resource;
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

        if (empty($triggers)) {
            return;
        }

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

        if (empty($triggers)) {
            return;
        }

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
