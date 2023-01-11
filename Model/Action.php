<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model;

use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\Mview\ActionInterface;
use MateuszMesek\DocumentDataIndexIndexer\Model\Dimension\Factory as DimensionFactory;
use MateuszMesek\DocumentDataIndexIndexer\Model\DimensionProvider\WithDocumentNameProvider;
use MateuszMesek\DocumentDataIndexIndexer\Model\DimensionProvider\WithNodePathsProvider;
use MateuszMesek\DocumentDataIndexMviewApi\Model\ChangelogListProviderInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\Data\ChangelogInterface;

class Action implements ActionInterface
{
    public function __construct(
        private readonly ChangelogListProviderInterface $changelogListProvider,
        private readonly DimensionFactory               $dimensionFactory,
        private readonly DimensionalIndexerInterface    $dimensionalIndexer,
        private readonly string                         $documentName
    )
    {
    }

    public function execute($ids): void
    {
        $context = ['document_name' => $this->documentName, 'changelog_ids' => $ids];

        $items = $this->changelogListProvider->get($context);

        foreach ($items as $item) {
            $dimensions = $this->buildDimensions($item);

            $this->dimensionalIndexer->executeByDimensions(
                $dimensions,
                $item->getIds()
            );
        }
    }

    private function buildDimensions(ChangelogInterface $changelog): array
    {
        $dimensions = [];

        foreach ($changelog->getDimensions() as $name => $value) {
            $dimensions[$name] = $this->dimensionFactory->create($name, $value);
        }

        $dimensions[WithDocumentNameProvider::DIMENSION_NAME] = $this->dimensionFactory->create(
            WithDocumentNameProvider::DIMENSION_NAME,
            $this->documentName
        );
        $dimensions[WithNodePathsProvider::DIMENSION_NAME] = $this->dimensionFactory->create(
            WithNodePathsProvider::DIMENSION_NAME,
            $changelog->getPaths()
        );

        return $dimensions;
    }
}
