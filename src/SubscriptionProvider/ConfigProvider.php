<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\SubscriptionProvider;

use MateuszMesek\DocumentDataIndexMview\Config;
use MateuszMesek\DocumentDataIndexMview\ContextReader;
use MateuszMesek\DocumentDataIndexMviewApi\SubscriptionProviderInterface;
use Traversable;

class ConfigProvider implements SubscriptionProviderInterface
{
    private ContextReader $contextReader;
    private Config $config;

    public function __construct(
        ContextReader $contextReader,
        Config $config
    )
    {
        $this->contextReader = $contextReader;
        $this->config = $config;
    }

    public function get(array $context): Traversable
    {
        $documentName = $this->contextReader->getDocumentName($context);

        yield from $this->config->getSubscriptions($documentName);
    }
}
