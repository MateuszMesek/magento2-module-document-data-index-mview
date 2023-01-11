<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview\Model;

use MateuszMesek\DocumentDataIndexMview\Model\ContextReader;
use MateuszMesek\DocumentDataIndexMviewApi\Model\ChangelogTableNameResolverInterface;

class ChangelogTableNameResolver implements ChangelogTableNameResolverInterface
{
    public function __construct(
        private readonly ContextReader $contextReader,
        private readonly string        $prefix,
        private readonly string        $suffix
    )
    {
    }

    public function resolve(array $context): string
    {
        $documentName = $this->contextReader->getDocumentName($context);

        return $this->prefix . $documentName . $this->suffix;
    }
}
