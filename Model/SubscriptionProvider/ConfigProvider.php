<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model\SubscriptionProvider;

use MateuszMesek\DocumentDataIndexMview\Model\Config;
use MateuszMesek\DocumentDataIndexMview\Model\ContextReader;
use MateuszMesek\DocumentDataIndexMviewApi\Model\SubscriptionProviderInterface;
use Traversable;

class ConfigProvider implements SubscriptionProviderInterface
{
    public function __construct(
        private readonly ContextReader $contextReader,
        private readonly Config        $config
    )
    {
    }

    public function get(array $context): Traversable
    {
        $documentName = $this->contextReader->getDocumentName($context);

        yield from $this->config->getSubscriptions($documentName);
    }
}
