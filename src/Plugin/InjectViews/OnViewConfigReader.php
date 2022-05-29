<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Plugin\InjectViews;

use Magento\Framework\Mview\Config\Reader;
use MateuszMesek\DocumentDataIndexMview\Config;
use MateuszMesek\DocumentDataIndexMviewApi\ChangelogTableNameResolverInterface;

class OnViewConfigReader
{
    private Config $config;
    private ChangelogTableNameResolverInterface $changelogTableNameResolver;

    public function __construct(
        Config $config,
        ChangelogTableNameResolverInterface $changelogTableNameResolver
    )
    {
        $this->config = $config;
        $this->changelogTableNameResolver = $changelogTableNameResolver;
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
                'walker' => 'Magento\Framework\Mview\View\ChangeLogBatchWalker',
                'subscriptions' => [
                    $changelogTableName => [
                        'name' => $changelogTableName,
                        'column' => 'id',
                        'subscription_model' => null,
                        'processor' => 'Magento\Framework\Mview\View\AdditionalColumnsProcessor\DefaultProcessor'
                    ]
                ],
                'document_name' => $documentName
            ];
        }

        return $output;
    }
}
