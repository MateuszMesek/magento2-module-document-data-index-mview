<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\NodeSubscription\Attribute;

use InvalidArgumentException;
use Magento\Eav\Model\Config;
use Magento\Framework\DB\Ddl\Trigger;
use MateuszMesek\DocumentDataIndexMview\Data\SubscriptionFactory;
use Traversable;

class SubscriptionGenerator
{
    private Config $config;
    private SubscriptionFactory $subscriptionFactory;

    public function __construct(
        Config $config,
        SubscriptionFactory $subscriptionFactory
    )
    {
        $this->config = $config;
        $this->subscriptionFactory = $subscriptionFactory;
    }

    public function generate(string $type, string $code): Traversable
    {
        $attribute = $this->config->getAttribute($type, $code);

        if (!$attribute) {
            throw new InvalidArgumentException("Attribute '$code' not found");
        }

        foreach (Trigger::getListOfEvents() as $event) {
            switch ($event) {
                case Trigger::EVENT_INSERT:
                case Trigger::EVENT_UPDATE:
                    $prefix = 'NEW';
                    break;

                case Trigger::EVENT_DELETE:
                    $prefix = 'OLD';
                    break;

                default:
                    throw new InvalidArgumentException("Trigger event '$event' is unsupported");
            }

            yield $this->subscriptionFactory->create([
                'tableName' => $attribute->getBackendTable(),
                'triggerEvent' => $event,
                'condition' => "$prefix.attribute_id = {$attribute->getAttributeId()}",
                'documentId' => "$prefix.entity_id",
                'dimensions' => "JSON_SET('{}', '$.scope', $prefix.store_id)",
            ]);
        }
    }
}
