<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Config\Converter;

use DOMNode;
use MateuszMesek\DocumentDataIndexMview\Config\Converter\NodeSubscription\Pool;
use MateuszMesek\Framework\Config\Converter\ProcessorInterface;

class NodeSubscription implements ProcessorInterface
{
    private Pool $pool;

    public function __construct(
        Pool $pool
    )
    {
        $this->pool = $pool;
    }

    public function process(DOMNode $node): array
    {
        return $this->pool->get($node->nodeName)->process($node);
    }
}
