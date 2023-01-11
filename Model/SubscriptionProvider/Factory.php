<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model\SubscriptionProvider;

use InvalidArgumentException;
use Magento\Framework\ObjectManagerInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\Config\SubscriptionProviderInterface as ConfigInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\SubscriptionProviderInterface;

class Factory
{
    private array $providerByDocumentName = [];

    public function __construct(
        private readonly ConfigInterface        $config,
        private readonly ObjectManagerInterface $objectManager,
        private readonly string                 $defaultType
    )
    {
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
