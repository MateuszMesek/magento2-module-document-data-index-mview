<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Config\Converter\NodeSubscription;

use InvalidArgumentException;
use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use MateuszMesek\Framework\Config\Converter\ProcessorInterface;

class Pool
{
    private TMap $types;

    public function __construct(
        TMapFactory $TMapFactory,
        array $types
    )
    {
        $this->types = $TMapFactory->createSharedObjectsMap([
            'type' => ProcessorInterface::class,
            'array' => $types
        ]);
    }

    public function get(string $type): ProcessorInterface
    {
        if (!$this->types->offsetExists($type)) {
            throw new InvalidArgumentException(sprintf(
                'The "%s" node subscription isn\'t defined.',
                $type
            ));
        }

        return $this->types->offsetGet($type);
    }
}
