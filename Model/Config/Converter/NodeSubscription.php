<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model\Config\Converter;

use DOMNode;
use MateuszMesek\DocumentDataIndexMview\Model\Config\Converter\NodeSubscription\Pool;
use MateuszMesek\Framework\Config\Converter\ProcessorInterface;

class NodeSubscription implements ProcessorInterface
{
    public function __construct(
        private readonly Pool $pool
    )
    {
    }

    public function process(DOMNode $node): array
    {
        return $this->pool->get($node->nodeName)->process($node);
    }
}
