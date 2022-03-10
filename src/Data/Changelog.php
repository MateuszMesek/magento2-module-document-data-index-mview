<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexerMview\Data;

use ArrayIterator;
use MateuszMesek\DocumentDataIndexerMviewApi\Data\ChangelogInterface;
use Traversable;

class Changelog implements ChangelogInterface
{
    private array $dimensions;
    private array $ids;
    private array $paths;

    public function __construct(
        array $dimensions,
        array $ids,
        array $paths
    )
    {
        $this->dimensions = $dimensions;
        $this->ids = $ids;
        $this->paths = $paths;
    }

    public function getDimensions(): array
    {
        return $this->dimensions;
    }

    public function getIds(): Traversable
    {
        return new ArrayIterator($this->ids);
    }

    public function getPaths(): array
    {
        return $this->paths;
    }
}
