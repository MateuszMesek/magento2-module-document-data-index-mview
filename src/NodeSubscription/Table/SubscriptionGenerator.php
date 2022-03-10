<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexerMview\NodeSubscription\Table;

use InvalidArgumentException;
use Magento\Framework\DB\Ddl\Trigger;
use MateuszMesek\DocumentDataIndexerMview\Data\SubscriptionFactory;
use Traversable;

class SubscriptionGenerator
{
    private SubscriptionFactory $subscriptionFactory;

    public function __construct(
        SubscriptionFactory $subscriptionFactory
    )
    {
        $this->subscriptionFactory = $subscriptionFactory;
    }

    public function generate(string $name, string $column): Traversable
    {
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
                'tableName' => $name,
                'triggerEvent' => $event,
                'documentId' => "$prefix.$column"
            ]);
        }
    }
}
