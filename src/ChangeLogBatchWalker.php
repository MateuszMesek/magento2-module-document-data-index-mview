<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mview\View\ChangeLogBatchWalkerInterface;
use Magento\Framework\Mview\View\ChangelogInterface;
use Magento\Framework\Mview\View\ChangelogTableNotExistsException;
use Magento\Framework\Phrase;

class ChangeLogBatchWalker implements ChangeLogBatchWalkerInterface
{
    private ResourceConnection $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     */
    public function walk(ChangelogInterface $changelog, int $fromVersionId, int $toVersion, int $batchSize)
    {
        $connection = $this->resourceConnection->getConnection();
        $changelogTableName = $this->resourceConnection->getTableName($changelog->getName());

        if (!$connection->isTableExists($changelogTableName)) {
            throw new ChangelogTableNotExistsException(new Phrase("Table %1 does not exist", [$changelogTableName]));
        }

        $select = $connection->select()
            ->distinct(true)
            ->from($changelogTableName, [$changelog->getColumnName()])
            ->where(
                'version_id > ?',
                $fromVersionId
            )
            ->where(
                'version_id <= ?',
                $toVersion
            )
            ->order('version_id ASC')
            ->limit($batchSize);

        return $connection->fetchCol($select);
    }
}
