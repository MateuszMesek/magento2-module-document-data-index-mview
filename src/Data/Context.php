<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Data;

use MateuszMesek\DocumentDataIndexMviewApi\Data\ContextInterface;

class Context implements ContextInterface
{
    private string $documentName;

    public function __construct(
        string $documentName
    )
    {
        $this->documentName = $documentName;
    }

    public function getDocumentName(): string
    {
        return $this->documentName;
    }
}
