<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use MateuszMesek\DocumentDataIndexMview\Model\Data\ChangelogFactory;
use MateuszMesek\DocumentDataIndexMview\Model\ContextReader;
use MateuszMesek\DocumentDataIndexMviewApi\Model\ChangelogListProviderInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\ChangelogTableNameResolverInterface;
use Traversable;

class ChangelogListProvider implements ChangelogListProviderInterface
{
    public function __construct(
        private readonly ContextReader                       $contextReader,
        private readonly ChangelogTableNameResolverInterface $changelogTableNameResolver,
        private readonly ResourceConnection                  $resourceConnection,
        private readonly ChangelogFactory                    $changelogFactory,
        private readonly Json                                $json
    )
    {
    }

    public function get(array $context): Traversable
    {
        $ids = $this->contextReader->getChangelogIds($context);
        $changelogTableName = $this->changelogTableNameResolver->resolve($context);

        $connection = $this->resourceConnection->getConnection();
        $select = ($connection->select())
            ->distinct(true)
            ->from(
                $this->resourceConnection->getTableName($changelogTableName),
                ['document_id', 'node_path', 'dimensions']
            )
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
                if (isset($paths['*'])) {
                    $paths = [];
                }

                yield $this->changelogFactory->create([
                    'dimensions' => $this->json->unserialize($dimensions),
                    'ids' => [$documentId],
                    'paths' => array_keys($paths)
                ]);
            }
        }
    }
}
