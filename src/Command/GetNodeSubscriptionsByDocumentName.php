<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Command;

use MateuszMesek\DocumentDataIndexMview\Command\GetNodeSubscriptionsByDocument\ResolverPool;
use MateuszMesek\DocumentDataIndexMviewApi\Command\GetNodeSubscriptionsByDocumentNameInterface;
use Traversable;

class GetNodeSubscriptionsByDocumentName implements GetNodeSubscriptionsByDocumentNameInterface
{
    private ResolverPool $resolverPool;

    public function __construct(
        ResolverPool $resolverPool
    )
    {
        $this->resolverPool = $resolverPool;
    }

    public function execute(string $documentName): Traversable
    {
        yield from $this->resolverPool->get($documentName)->resolve();
    }
}
