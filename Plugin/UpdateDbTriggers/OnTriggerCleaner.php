<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Plugin\UpdateDbTriggers;

use Generator;
use Magento\Framework\Mview\TriggerCleaner;
use Magento\Framework\Mview\View\CollectionFactory;
use Magento\Framework\Mview\View\StateInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\DocumentNameResolverInterface;
use MateuszMesek\DocumentDataIndexMviewApi\Model\TriggerProviderInterface;
use ReflectionObject;
use ReflectionProperty;

class OnTriggerCleaner
{
    public function __construct(
        private readonly CollectionFactory             $viewCollectionFactory,
        private readonly DocumentNameResolverInterface $documentNameResolver,
        private readonly TriggerProviderInterface      $triggerProvider,
    )
    {
    }

    public function beforeRemoveTriggers(
        TriggerCleaner $triggerCleaner
    ): void
    {
        $processedTriggers = [];
        $contexts = $this->getContexts();

        foreach ($contexts as $context) {
            $triggers = $this->triggerProvider->get($context);

            foreach ($triggers as $trigger) {
                $processedTriggers[$trigger->getName()] = true;
            }
        }

        $this->updateProcessedTriggers($triggerCleaner, $processedTriggers);
    }

    private function getContexts(): Generator
    {
        $viewCollection = $this->viewCollectionFactory->create();

        $viewList = $viewCollection->getViewsByStateMode(StateInterface::MODE_ENABLED);

        foreach ($viewList as $view) {
            $context = [
                'view_id' => $view->getId()
            ];

            $documentName = $this->documentNameResolver->resolver($context);

            if (null === $documentName) {
                continue;
            }

            $context['document_name'] = $documentName;

            yield $context;
        }
    }

    private function updateProcessedTriggers(TriggerCleaner $triggerCleaner, array $toUpdate): void
    {
        if (empty($toUpdate)) {
            return;
        }

        $property = $this->getProperty($triggerCleaner);

        $processedTriggers = $property->getValue($triggerCleaner);
        $processedTriggers = array_merge(
            $processedTriggers,
            $toUpdate
        );

        $property->setValue($triggerCleaner, $processedTriggers);
    }

    private function getProperty(TriggerCleaner $triggerCleaner): ReflectionProperty
    {
        $reflection = new ReflectionObject($triggerCleaner);

        while (!$reflection->hasProperty('processedTriggers')) {
            $reflection = $reflection->getParentClass();
        }

        $property = $reflection->getProperty('processedTriggers');
        $property->setAccessible(true);

        return $property;
    }
}
