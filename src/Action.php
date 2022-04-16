<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview;

use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\Mview\ActionInterface;
use MateuszMesek\DocumentDataIndexIndexer\DimensionProvider\Factory as DimensionFactory;
use MateuszMesek\DocumentDataIndexIndexer\DimensionProvider\WithDocumentNameProvider;
use MateuszMesek\DocumentDataIndexIndexer\DimensionProvider\WithNodePathsProvider;
use MateuszMesek\DocumentDataIndexMviewApi\ChangelogListProviderInterface;

class Action implements ActionInterface
{
    private ChangelogListProviderInterface $changelogListProvider;
    private DimensionFactory $dimensionFactory;
    private DimensionalIndexerInterface $dimensionalIndexer;
    private string $documentName;

    public function __construct(
        ChangelogListProviderInterface $changelogListProvider,
        DimensionFactory $dimensionFactory,
        DimensionalIndexerInterface $dimensionalIndexer,
        string $documentName
    )
    {
        $this->changelogListProvider = $changelogListProvider;
        $this->dimensionFactory = $dimensionFactory;
        $this->dimensionalIndexer = $dimensionalIndexer;
        $this->documentName = $documentName;
    }

    public function execute($ids): void
    {
        $context = ['document_name' => $this->documentName, 'changelog_ids' => $ids];

        $items = $this->changelogListProvider->get($context);

        foreach ($items as $item) {
            $dimensions = [
                WithDocumentNameProvider::DIMENSION_NAME => $this->dimensionFactory->create(
                    WithDocumentNameProvider::DIMENSION_NAME,
                    $this->documentName
                ),
                WithNodePathsProvider::DIMENSION_NAME => $this->dimensionFactory->create(
                    WithNodePathsProvider::DIMENSION_NAME,
                    $item->getPaths()
                )
            ];

            foreach ($item->getDimensions() as $name => $value) {
                $dimensions[$name] = $this->dimensionFactory->create($name, $value);
            }

            $this->dimensionalIndexer->executeByDimensions(
                $dimensions,
                $item->getIds()
            );
        }
    }
}
