<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexerMview\Plugin\UpdateDbTriggers;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mview\View\SubscriptionInterface;
use Magento\Framework\Mview\ViewInterface;
use MateuszMesek\DocumentDataIndexerMviewApi\Command\GetDocumentNameByViewIdInterface;
use MateuszMesek\DocumentDataIndexerMviewApi\Command\GetTriggersByDocumentNameInterface;

class OnSubscription
{
    private GetDocumentNameByViewIdInterface $getDocumentNameByViewId;
    private GetTriggersByDocumentNameInterface $getTriggersByDocumentName;
    private ResourceConnection $resource;

    public function __construct(
        GetDocumentNameByViewIdInterface $getDocumentNameByViewId,
        GetTriggersByDocumentNameInterface $getTriggersByDocumentName,
        ResourceConnection $resource
    )
    {
        $this->getDocumentNameByViewId = $getDocumentNameByViewId;
        $this->getTriggersByDocumentName = $getTriggersByDocumentName;
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

        $documentName = $this->getDocumentNameByViewId->execute($view->getId());

        if (null === $documentName) {
            return;
        }

        $triggers = $this->getTriggersByDocumentName->execute($documentName);

        if (empty($triggers)) {
            return;
        }

        $connection = $this->resource->getConnection();
        $connection->query(sprintf(
            <<<SQL
                CREATE TABLE IF NOT EXISTS %s LIKE %s
            SQL,
            $this->resource->getTableName("document_data_{$documentName}_mview"),
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

        $documentName = $this->getDocumentNameByViewId->execute($view->getId());

        if (null === $documentName) {
            return;
        }

        $triggers = $this->getTriggersByDocumentName->execute($documentName);

        if (empty($triggers)) {
            return;
        }

        $connection = $this->resource->getConnection();

        foreach ($triggers as $trigger) {
            $connection->dropTrigger($trigger->getName());
        }

        $connection->dropTable(
            $this->resource->getTableName("document_data_{$documentName}_mview")
        );
    }
}
