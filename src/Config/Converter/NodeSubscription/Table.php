<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Config\Converter\NodeSubscription;

use DOMNode;
use MateuszMesek\DocumentDataIndexMview\NodeSubscription\Table\SubscriptionGenerator;
use MateuszMesek\Framework\Config\Converter\AttributeValueResolver;
use MateuszMesek\Framework\Config\Converter\ProcessorInterface;

class Table implements ProcessorInterface
{
    private AttributeValueResolver $attributeValueResolver;

    public function __construct(
        AttributeValueResolver $attributeValueResolver
    )
    {
        $this->attributeValueResolver = $attributeValueResolver;
    }

    public function process(DOMNode $node): array
    {
        $name = $this->attributeValueResolver->resolve($node, 'name');
        $column = $this->attributeValueResolver->resolve($node, 'column');

        return [
            'id' => "table_{$name}_$column",
            'type' => SubscriptionGenerator::class,
            'arguments' => [
                $name,
                $column
            ]
        ];
    }
}
