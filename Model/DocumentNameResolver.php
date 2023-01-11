<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model;

use Magento\Framework\Mview\ConfigInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\DocumentNameResolverInterface;

class DocumentNameResolver implements DocumentNameResolverInterface
{
    public function __construct(
        private readonly ContextReader   $contextReader,
        private readonly ConfigInterface $config,
        private readonly string          $key
    )
    {
    }

    public function resolver(array $context): ?string
    {
        $viewId = $this->contextReader->getViewId($context);

        $config = $this->config->getView($viewId);

        return $config[$this->key] ?? null;
    }
}
