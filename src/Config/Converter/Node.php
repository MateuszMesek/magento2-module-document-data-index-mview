<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexerMview\Config\Converter;

use DOMNode;
use MateuszMesek\Framework\Config\Converter\AttributeValueResolver;
use MateuszMesek\Framework\Config\Converter\ChildrenResolver;
use MateuszMesek\Framework\Config\Converter\ProcessorInterface;

class Node implements ProcessorInterface
{
    private AttributeValueResolver $attributeValueResolver;
    private ChildrenResolver $childrenResolver;
    private ProcessorInterface $nodeSubscriptionProcessor;

    public function __construct(
        AttributeValueResolver $attributeValueResolver,
        ChildrenResolver $childrenResolver,
        ProcessorInterface $nodeSubscriptionProcessor
    )
    {
        $this->attributeValueResolver = $attributeValueResolver;
        $this->childrenResolver = $childrenResolver;
        $this->nodeSubscriptionProcessor = $nodeSubscriptionProcessor;
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
