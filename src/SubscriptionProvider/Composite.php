<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\SubscriptionProvider;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use MateuszMesek\DocumentDataIndexMviewApi\SubscriptionProviderInterface;
use Traversable;

class Composite implements SubscriptionProviderInterface
{
    /**
     * @var \Magento\Framework\ObjectManager\TMap|\MateuszMesek\DocumentDataIndexMviewApi\SubscriptionProviderInterface[]
     */
    private TMap $providers;

    public function __construct(
        TMapFactory $TMapFactory,
        array $providers
    )
    {
        $this->providers = $TMapFactory->createSharedObjectsMap([
            'type' => SubscriptionProviderInterface::class,
            'array' => $providers
        ]);
    }

    public function get(array $context): Traversable
    {
        $subscriptionByPath = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->get($context) as $path => $subscriptions) {
                if (!isset($subscriptionByPath[$path])) {
                    $subscriptionByPath[$path] = [];
                }

                foreach ($subscriptions as $subscriptionId => $subscription) {
                    if (isset($subscriptionByPath[$path][$subscriptionId])) {
                        continue;
                    }

                    $subscriptionByPath[$path][$subscriptionId] = $subscription;
                }
            }
        }

        yield from $subscriptionByPath;
    }
}
