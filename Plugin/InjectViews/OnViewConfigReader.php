<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Plugin\InjectViews;

use Magento\Framework\Mview\Config\Reader;
use Magento\Framework\Mview\View\AdditionalColumnsProcessor\DefaultProcessor;
use Magento\Framework\Mview\View\ChangeLogBatchWalker;
use MateuszMesek\DocumentDataIndexMview\Model\Config;
use MateuszMesek\DocumentDataIndexMviewApi\Model\ChangelogTableNameResolverInterface;

class OnViewConfigReader
{
    public function __construct(
        private readonly Config                              $config,
        private readonly ChangelogTableNameResolverInterface $changelogTableNameResolver
    )
    {
    }

    public function afterRead(
        Reader $reader,
        array  $output,
               $scope = null
    ): array
    {
        $documentNames = $this->config->getDocumentNames();

        foreach ($documentNames as $documentName) {
            $context = ['document_name' => $documentName];

            $viewId = "document_data_$documentName";
            $changelogTableName = $this->changelogTableNameResolver->resolve($context);

            $output[$viewId] = [
                'view_id' => $viewId,
                'action_class' => $this->config->getAction($documentName),
                'group' => 'indexer',
                'walker' => ChangeLogBatchWalker::class,
                'subscriptions' => [
                    $changelogTableName => [
                        'name' => $changelogTableName,
                        'column' => 'id',
                        'subscription_model' => null,
                        'processor' => DefaultProcessor::class
                    ]
                ],
                'document_name' => $documentName
            ];
        }

        return $output;
    }
}
