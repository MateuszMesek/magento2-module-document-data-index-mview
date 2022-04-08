<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Plugin\LimitNodePaths;

use MateuszMesek\DocumentDataApi\Command\GetDocumentDataInterface;
use MateuszMesek\DocumentDataApi\InputInterface;

class OnGetDocumentData
{
    private State $state;

    public function __construct(
        State $state
    )
    {
        $this->state = $state;
    }

    public function aroundExecute(
        GetDocumentDataInterface $getDocumentData,
        callable $proceed,
        string $documentName,
        InputInterface $input
    ): array
    {
        try {
            $this->state->setDocument($documentName, $input->getId());

            return $proceed($documentName, $input);
        } finally {
            $this->state->unsetDocument();
        }
    }
}
