<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model;

use Magento\Framework\App\ResourceConnection;
use MateuszMesek\DocumentDataIndexMview\Model\ContextReader;
use MateuszMesek\DocumentDataIndexMviewApi\Model\Data\SubscriptionInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\TriggerNameResolverInterface;

class TriggerNameResolver implements TriggerNameResolverInterface
{
    public function __construct(
        private readonly ContextReader      $contextReader,
        private readonly ResourceConnection $resourceConnection,
        private readonly string             $prefix
    )
    {
    }

    public function resolver(array $context, SubscriptionInterface $subscription): string
    {
        $documentName = $this->contextReader->getDocumentName($context);
        $tableName = $this->prefix . $documentName . '_' . $subscription->getTableName();

        return $this->resourceConnection->getTriggerName(
            $tableName,
            $subscription->getTriggerTime(),
            $subscription->getTriggerEvent()
        );
    }
}
