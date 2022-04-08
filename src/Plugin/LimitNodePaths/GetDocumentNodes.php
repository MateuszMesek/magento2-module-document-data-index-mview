<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Plugin\LimitNodePaths;

use MateuszMesek\DocumentDataApi\Command\GetDocumentNodesInterface;
use Traversable;

class GetDocumentNodes
{
    private State $state;

    public function __construct(
        State $state
    )
    {
        $this->state = $state;
    }

    public function afterExecute(
        GetDocumentNodesInterface $getDocumentNodes,
        Traversable $nodes,
        string $documentName
    ): Traversable
    {
        if (!$this->state->isLockedDocumentName($documentName)) {
            yield from $nodes;

            return;
        }

        foreach ($nodes as $node) {
            if (!$this->state->isLockedPath($documentName, $node['path'])) {
                continue;
            }

            yield $node;
        }
    }
}
