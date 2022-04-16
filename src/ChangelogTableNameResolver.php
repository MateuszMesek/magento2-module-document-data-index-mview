<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview;

use MateuszMesek\DocumentDataIndexMviewApi\ChangelogTableNameResolverInterface;

class ChangelogTableNameResolver implements ChangelogTableNameResolverInterface
{
    private ContextReader $contextReader;
    private string $prefix;
    private string $suffix;

    public function __construct(
        ContextReader $contextReader,
        string $prefix,
        string $suffix
    )
    {
        $this->contextReader = $contextReader;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    public function resolve(array $context): string
    {
        $documentName = $this->contextReader->getDocumentName($context);

        return $this->prefix.$documentName.$this->suffix;
    }
}
