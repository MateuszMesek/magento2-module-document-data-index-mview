<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview;

use Magento\Framework\Mview\ConfigInterface;
use MateuszMesek\DocumentDataIndexMviewApi\DocumentNameResolverInterface;

class DocumentNameResolver implements DocumentNameResolverInterface
{
    private ContextReader $contextReader;
    private ConfigInterface $config;
    private string $key;

    public function __construct(
        ContextReader $contextReader,
        ConfigInterface $config,
        string $key
    )
    {
        $this->contextReader = $contextReader;
        $this->config = $config;
        $this->key = $key;
    }

    public function resolver(array $context): ?string
    {
        $viewId = $this->contextReader->getViewId($context);

        $config = $this->config->getView($viewId);

        return $config[$this->key] ?? null;
    }
}
