<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview;

use Magento\Framework\Indexer\DimensionalIndexerInterface;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Mview\ActionInterface;
use MateuszMesek\DocumentDataIndex\DimensionProvider\WithDocumentNameProvider;
use MateuszMesek\DocumentDataIndex\DimensionProvider\WithNodePathsProvider;
use MateuszMesek\DocumentDataIndexMview\Plugin\LimitNodePaths\State;
use MateuszMesek\DocumentDataIndexMviewApi\Command\GetChangelogListInterface;

class Action implements ActionInterface
{
    private GetChangelogListInterface $getChangelogList;
    private DimensionalIndexerInterface $dimensionalIndexer;
    private DimensionFactory $dimensionFactory;
    private string $documentName;
    private State $state;

    public function __construct(
        GetChangelogListInterface $getChangelogList,
        DimensionalIndexerInterface $dimensionalIndexer,
        DimensionFactory $dimensionFactory,
        string $documentName,
        State $state
    )
    {
        $this->getChangelogList = $getChangelogList;
        $this->dimensionalIndexer = $dimensionalIndexer;
        $this->dimensionFactory = $dimensionFactory;
        $this->documentName = $documentName;
        $this->state = $state;
    }

    public function execute($ids): void
    {
        $items = $this->getChangelogList->execute($ids);

        foreach ($items as $item) {
            $dimensions = [
                WithDocumentNameProvider::DIMENSION_NAME => $this->dimensionFactory->create(
                    WithDocumentNameProvider::DIMENSION_NAME,
                    $this->documentName
                ),
            ];

            foreach ($item->getDimensions() as $name => $value) {
                $dimensions[$name] = $this->dimensionFactory->create($name, (string)$value);
            }

            try {
                $this->state->lock($this->documentName, (array)$item->getIds(), $item->getPaths());

                $this->dimensionalIndexer->executeByDimensions(
                    $dimensions,
                    $item->getIds()
                );
            } finally {
                $this->state->unlock();
            }
        }
    }
}
