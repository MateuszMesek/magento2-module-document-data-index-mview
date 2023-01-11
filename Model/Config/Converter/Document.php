<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model\Config\Converter;

use DOMNode;
use MateuszMesek\Framework\Config\Converter\AttributeValueResolver;
use MateuszMesek\Framework\Config\Converter\ChildrenResolver;
use MateuszMesek\Framework\Config\Converter\ProcessorInterface;

class Document implements ProcessorInterface
{
    private const NODES = [
        'action',
        'subscriptionProvider',
        'node'
    ];

    public function __construct(
        private readonly AttributeValueResolver $attributeValueResolver,
        private readonly ChildrenResolver       $childrenResolver,
        private readonly ProcessorInterface     $nodeProcessor
    )
    {
    }

    public function process(DOMNode $node): array
    {
        $data = [
            'name' => $this->attributeValueResolver->resolve($node, 'name'),
            'action' => null,
            'nodes' => [],
        ];

        foreach ($this->childrenResolver->resolve($node) as $child) {
            if (!in_array($child->nodeName, self::NODES, true)) {
                continue;
            }

            switch ($child->nodeName) {
                case 'action':
                case 'subscriptionProvider':
                    $data[$child->nodeName] = $this->attributeValueResolver->resolve($child, 'name');
                    break;

                case 'node':
                    $node = $this->nodeProcessor->process($child);

                    $data['subscriptions'][$node['path']] = $node['subscriptions'];
                    break;
            }
        }

        return $data;
    }
}
