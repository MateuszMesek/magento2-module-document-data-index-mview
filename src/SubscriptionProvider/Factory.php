<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\SubscriptionProvider;

use InvalidArgumentException;
use Magento\Framework\ObjectManagerInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Config\SubscriptionProviderInterface as ConfigInterface;
use MateuszMesek\DocumentDataIndexMviewApi\SubscriptionProviderInterface;

class Factory
{
    private ConfigInterface $config;
    private ObjectManagerInterface $objectManager;
    private string $defaultType;

    private array $providerByDocumentName = [];

    public function __construct(
        ConfigInterface $config,
        ObjectManagerInterface $objectManager,
        string $defaultType
    )
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->defaultType = $defaultType;
    }

    public function create(string $documentName): SubscriptionProviderInterface
    {
        $type = $this->config->getSubscriptionProvider($documentName);

        if (null === $type) {
            $type = $this->defaultType;
        }

        $provider = $this->objectManager->create($type);

        if (!$provider instanceof SubscriptionProviderInterface) {
            $interfaceName = SubscriptionProviderInterface::class;

            throw new InvalidArgumentException(
                "$type doesn't implement $interfaceName"
            );
        }

        return $provider;
    }

    public function get(string $documentName): SubscriptionProviderInterface
    {
        if (!isset($this->providerByDocumentName[$documentName])) {
            $this->providerByDocumentName[$documentName] = $this->create($documentName);
        }

        return $this->providerByDocumentName[$documentName];
    }
}
