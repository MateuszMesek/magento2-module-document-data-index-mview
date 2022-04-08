<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Command\GetNodeSubscriptionsByDocument;

use Generator;
use MateuszMesek\DocumentDataIndexMview\Config;
use MateuszMesek\DocumentDataIndexMviewApi\NodeSubscriptionsResolverInterface;

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
