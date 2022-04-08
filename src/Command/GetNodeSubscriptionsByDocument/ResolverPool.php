<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Command\GetNodeSubscriptionsByDocument;

use InvalidArgumentException;
use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use MateuszMesek\DocumentDataIndexMviewApi\NodeSubscriptionsResolverInterface;

class ResolverPool
{
    private TMap $documents;

    public function __construct(
        TMapFactory $TMapFactory,
        array $documents = []
    )
    {
        $this->documents = $TMapFactory->createSharedObjectsMap([
            'type' => NodeSubscriptionsResolverInterface::class,
            'array' => $documents
        ]);
    }

    public function get(string $documentName): NodeSubscriptionsResolverInterface
    {
        $resolver = $this->documents[$documentName];

        if (!$resolver instanceof NodeSubscriptionsResolverInterface) {
            throw new InvalidArgumentException("Node subscription resolver for '$documentName' not found");
        }

        return $resolver;
    }
}
