<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexerMview\Command\GetNodeSubscriptionsByDocument;

use Generator;
use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use MateuszMesek\DocumentDataIndexerMviewApi\NodeSubscriptionsResolverInterface;

class CompositeResolver implements NodeSubscriptionsResolverInterface
{
    /**
     * @var TMap|NodeSubscriptionsResolverInterface[]
     */
    private TMap $resolvers;

    public function __construct(
        TMapFactory $TMapFactory,
        array $resolvers
    )
    {
        $this->resolvers = $TMapFactory->createSharedObjectsMap([
            'type' => NodeSubscriptionsResolverInterface::class,
            'array' => $resolvers
        ]);
    }

    public function resolve(): Generator
    {
        $paths = [];

        foreach ($this->resolvers as $resolver) {
            foreach ($resolver->resolve() as $path => $subscriptions) {
                if (!isset($paths[$path])) {
                    $paths[$path] = [];
                }

                foreach ($subscriptions as $subscriptionId => $subscription) {
                    if (isset($paths[$path][$subscriptionId])) {
                        continue;
                    }

                    $paths[$path][$subscriptionId] = $subscription;
                }
            }
        }

        yield from $paths;
    }
}
