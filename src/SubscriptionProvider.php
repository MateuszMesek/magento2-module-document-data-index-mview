<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview;

use MateuszMesek\DocumentDataIndexMview\SubscriptionProvider\Factory;
use MateuszMesek\DocumentDataIndexMviewApi\SubscriptionProviderInterface;
use Traversable;

class SubscriptionProvider implements SubscriptionProviderInterface
{
    private ContextReader $contextReader;
    private Factory $factory;

    public function __construct(
        ContextReader $contextReader,
        Factory $factory
    )
    {
        $this->contextReader = $contextReader;
        $this->factory = $factory;
    }

    public function get(array $context): Traversable
    {
        $documentName = $this->contextReader->getDocumentName($context);

        return $this->factory->get($documentName)->get($context);
    }
}
