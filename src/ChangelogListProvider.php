<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use MateuszMesek\DocumentDataIndexMview\Data\ChangelogFactory;
use MateuszMesek\DocumentDataIndexMviewApi\ChangelogListProviderInterface;
use MateuszMesek\DocumentDataIndexMviewApi\ChangelogTableNameResolverInterface;
use Traversable;

class ChangelogListProvider implements ChangelogListProviderInterface
{
    private ContextReader $contextReader;
    private ChangelogTableNameResolverInterface $changelogTableNameResolver;
    private ResourceConnection $resourceConnection;
    private ChangelogFactory $changelogFactory;
    private Json $json;

    public function __construct(
        ContextReader $contextReader,
        ChangelogTableNameResolverInterface $changelogTableNameResolver,
        ResourceConnection $resourceConnection,
        ChangelogFactory $changelogFactory,
        Json $json
    )
    {
        $this->contextReader = $contextReader;
        $this->changelogTableNameResolver = $changelogTableNameResolver;
        $this->resourceConnection = $resourceConnection;
        $this->changelogFactory = $changelogFactory;
        $this->json = $json;
    }

    public function get(array $context): Traversable
    {
        $ids = $this->contextReader->getChangelogIds($context);
        $changelogTableName = $this->changelogTableNameResolver->resolve($context);

        $connection = $this->resourceConnection->getConnection();
        $select = ($connection->select())
            ->from($this->resourceConnection->getTableName($changelogTableName))
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
                    'dimensions' => $this->json->unserialize($dimensions),
                    'ids' => [$documentId],
                    'paths' => array_keys($paths)
                ]);
            }
        }
    }
}
