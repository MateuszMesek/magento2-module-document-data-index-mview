<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model\Config\Converter\NodeSubscription;

use DOMNode;
use MateuszMesek\DocumentDataIndexMview\Model\NodeSubscription\Attribute\SubscriptionGenerator;
use MateuszMesek\Framework\Config\Converter\AttributeValueResolver;
use MateuszMesek\Framework\Config\Converter\ProcessorInterface;

class Attribute implements ProcessorInterface
{
    public function __construct(
        private readonly AttributeValueResolver $attributeValueResolver
    )
    {
    }

    public function process(DOMNode $node): array
    {
        $type = $this->attributeValueResolver->resolve($node, 'type');
        $code = $this->attributeValueResolver->resolve($node, 'code');

        return [
            'id' => "attribute_{$type}_$code",
            'type' => SubscriptionGenerator::class,
            'arguments' => [
                $type,
                $code,
            ]
        ];
    }
}
