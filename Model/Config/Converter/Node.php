<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model\Config\Converter;

use DOMNode;
use MateuszMesek\Framework\Config\Converter\AttributeValueResolver;
use MateuszMesek\Framework\Config\Converter\ChildrenResolver;
use MateuszMesek\Framework\Config\Converter\ProcessorInterface;

class Node implements ProcessorInterface
{
    public function __construct(
        private readonly AttributeValueResolver $attributeValueResolver,
        private readonly ChildrenResolver       $childrenResolver,
        private readonly ProcessorInterface     $nodeSubscriptionProcessor
    )
    {
    }

    public function process(DOMNode $node): array
    {
        $data = [
            'path' => $this->attributeValueResolver->resolve($node, 'path'),
            'subscriptions' => [],
        ];

        foreach ($this->childrenResolver->resolve($node) as $child) {
            $subscription = $this->nodeSubscriptionProcessor->process($child);

            $data['subscriptions'][$subscription['id']] = $subscription;
        }

        return $data;
    }
}
