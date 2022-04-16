<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview;

use Magento\Framework\Config\DataInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Config\SubscriptionProviderInterface;

class Config implements SubscriptionProviderInterface
{
    private DataInterface $data;

    public function __construct(
        DataInterface $data
    )
    {
        $this->data = $data;
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
