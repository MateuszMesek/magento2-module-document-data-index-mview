<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview;

use Magento\Framework\App\ResourceConnection;
use MateuszMesek\DocumentDataIndexMviewApi\Data\SubscriptionInterface;
use MateuszMesek\DocumentDataIndexMviewApi\TriggerNameResolverInterface;

class TriggerNameResolver implements TriggerNameResolverInterface
{
    private ContextReader $contextReader;
    private ResourceConnection $resourceConnection;
    private string $prefix;

    public function __construct(
        ContextReader $contextReader,
        ResourceConnection $resourceConnection,
        string $prefix
    )
    {
        $this->contextReader = $contextReader;
        $this->resourceConnection = $resourceConnection;
        $this->prefix = $prefix;
    }

    public function resolver(array $context, SubscriptionInterface $subscription): string
    {
        $documentName = $this->contextReader->getDocumentName($context);
        $tableName = $this->prefix.$documentName.'_'.$subscription->getTableName();

        return $this->resourceConnection->getTriggerName(
            $tableName,
            $subscription->getTriggerTime(),
            $subscription->getTriggerEvent()
        );
    }
}
