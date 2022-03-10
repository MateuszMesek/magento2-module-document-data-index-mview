<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexerMview\Command;

use MateuszMesek\DocumentDataIndexerMview\Command\GetNodeSubscriptionsByDocument\ResolverPool;
use MateuszMesek\DocumentDataIndexerMviewApi\Command\GetNodeSubscriptionsByDocumentNameInterface;
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
