<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexerMview\Command\GetNodeSubscriptionsByDocument;

use Generator;
use MateuszMesek\DocumentDataIndexerMview\Config;
use MateuszMesek\DocumentDataIndexerMviewApi\NodeSubscriptionsResolverInterface;

class ConfigResolver implements NodeSubscriptionsResolverInterface
{
    private Config $config;
    private string $documentName;

    public function __construct(
        Config $config,
        string $documentName
    )
    {
        $this->config = $config;
        $this->documentName = $documentName;
    }

    public function resolve(): Generator
    {
        yield from $this->config->getSubscriptions($this->documentName);
    }
}
