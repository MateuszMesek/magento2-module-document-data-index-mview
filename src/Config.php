<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexerMview;

use Magento\Framework\Config\DataInterface;

class Config
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

    public function getSubscriptions(string $documentName): array
    {
        return $this->data->get("$documentName/subscriptions") ?: [];
    }
}
