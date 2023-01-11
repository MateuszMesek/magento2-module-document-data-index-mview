<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model\Config\Converter\NodeSubscription;

use DOMNode;
use MateuszMesek\DocumentDataIndexMview\Model\NodeSubscription\Table\SubscriptionGenerator;
use MateuszMesek\Framework\Config\Converter\AttributeValueResolver;
use MateuszMesek\Framework\Config\Converter\ProcessorInterface;

class Table implements ProcessorInterface
{
    public function __construct(
        private readonly AttributeValueResolver $attributeValueResolver
    )
    {
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
