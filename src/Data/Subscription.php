<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexerMview\Data;

use Magento\Framework\DB\Ddl\Trigger;
use MateuszMesek\DocumentDataIndexerMviewApi\Data\SubscriptionInterface;

class Subscription implements SubscriptionInterface
{
    private string $tableName;
    private string $triggerEvent;
    private string $triggerTime;
    private ?string $condition;
    private string $documentId;
    private ?string $dimensions;

    public function __construct(
        string $tableName,
        string $triggerEvent,
        string $documentId,
        string $triggerTime = Trigger::TIME_AFTER,
        ?string $condition = null,
        ?string $dimensions = null
    )
    {
        $this->tableName = $tableName;
        $this->triggerEvent = $triggerEvent;
        $this->triggerTime = $triggerTime;
        $this->condition = $condition;
        $this->documentId = $documentId;
        $this->dimensions = $dimensions;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getTriggerEvent(): string
    {
        return $this->triggerEvent;
    }

    public function getTriggerTime(): string
    {
        return $this->triggerTime;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function getDocumentId(): string
    {
        return $this->documentId;
    }

    public function getDimensions(): ?string
    {
        return $this->dimensions;
    }
}
