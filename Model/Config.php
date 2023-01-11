<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model;

use Magento\Framework\Config\DataInterface;
use MateuszMesek\DocumentDataIndexMview\Model\Action;
use MateuszMesek\DocumentDataIndexMviewApi\Model\Config\SubscriptionProviderInterface;

class Config implements SubscriptionProviderInterface
{
    public function __construct(
        private readonly DataInterface $data
    )
    {
    }

    public function getDocumentNames(): array
    {
        $documents = $this->data->get();

        return array_keys($documents);
    }

    public function getAction(string $documentName): string
    {
        $action = $this->data->get("$documentName/action");

        if (null === $action) {
            $action = Action::class;
        }

        return $action;
    }

    public function getSubscriptionProvider(string $documentName): ?string
    {
        return $this->data->get("$documentName/subscriptionProvider");
    }

    public function getSubscriptions(string $documentName): array
    {
        return $this->data->get("$documentName/subscriptions") ?: [];
    }
}
