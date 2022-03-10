<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexerMview\Command;

use Magento\Framework\Mview\ConfigInterface;
use MateuszMesek\DocumentDataIndexerMviewApi\Command\GetDocumentNameByViewIdInterface;

class GetDocumentNameByViewId implements GetDocumentNameByViewIdInterface
{
    private ConfigInterface $config;

    public function __construct(
        ConfigInterface $config
    )
    {
        $this->config = $config;
    }

    public function execute(string $viewId): ?string
    {
        $config = $this->config->getView($viewId);

        return $config['document_name'] ?? null;
    }
}
