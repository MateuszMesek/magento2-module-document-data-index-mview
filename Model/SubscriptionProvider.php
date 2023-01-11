<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model;

use MateuszMesek\DocumentDataIndexMview\Model\ContextReader;
use MateuszMesek\DocumentDataIndexMview\Model\SubscriptionProvider\Factory;
use MateuszMesek\DocumentDataIndexMviewApi\Model\SubscriptionProviderInterface;
use Traversable;

class SubscriptionProvider implements SubscriptionProviderInterface
{
    public function __construct(
        private readonly ContextReader $contextReader,
        private readonly Factory       $factory
    )
    {
    }

    public function get(array $context): Traversable
    {
        $documentName = $this->contextReader->getDocumentName($context);

        return $this->factory->get($documentName)->get($context);
    }
}
