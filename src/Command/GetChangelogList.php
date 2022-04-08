<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Command;

use Magento\Framework\App\ResourceConnection;
use MateuszMesek\DocumentDataIndexMview\Data\ChangelogFactory;
use MateuszMesek\DocumentDataIndexMviewApi\Command\GetChangelogListInterface;
use Traversable;

class GetChangelogList implements GetChangelogListInterface
{
    private ResourceConnection $resourceConnection;
    private string $documentName;
    private ChangelogFactory $changelogFactory;

    public function __construct(
        ResourceConnection $resourceConnection,
        string $documentName,
        ChangelogFactory $changelogFactory
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->documentName = $documentName;
        $this->changelogFactory = $changelogFactory;
    }

    public function execute(array $ids): Traversable
    {
        $connection = $this->resourceConnection->getConnection();
        $select = ($connection->select())
            ->from($this->resourceConnection->getTableName("document_data_{$this->documentName}_mview"))
            ->where('id IN (?)', $ids);

        $rows = $connection->fetchAll($select);

        $groupByDimensions = [];

        foreach ($rows as $row) {
            ['document_id' => $documentId, 'node_path' => $path, 'dimensions' => $dimensions] = $row;

            if (!isset($groupByDimensions[$dimensions])) {
                $groupByDimensions[$dimensions] = [];
            }

            if (!isset($groupByDimensions[$dimensions][$documentId])) {
                $groupByDimensions[$dimensions][$documentId] = [];
            }

            $groupByDimensions[$dimensions][$documentId][$path] = true;
        }

        foreach ($groupByDimensions as $dimensions => $documentIds) {
            foreach ($documentIds as $documentId => $paths) {
                if (isset($paths[null])) {
                    $paths = [];
                }

                yield $this->changelogFactory->create([
                    'dimensions' => json_decode($dimensions, true),
                    'ids' => [$documentId],
                    'paths' => array_keys($paths)
                ]);
            }
        }
    }
}
