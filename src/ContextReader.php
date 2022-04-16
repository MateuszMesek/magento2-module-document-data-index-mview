<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexMview;

use InvalidArgumentException;

class ContextReader
{
    public function getDocumentName(array $context): string
    {
        if (!isset($context['document_name'])) {
            throw new InvalidArgumentException('"document_name" is not provided in context');
        }

        return $context['document_name'];
    }

    public function getViewId(array $context): string
    {
        if (!isset($context['view_id'])) {
            throw new InvalidArgumentException('"view_id" is not provided in context');
        }

        return $context['view_id'];
    }

    public function getChangelogIds(array $context): array
    {
        if (!isset($context['changelog_ids'])) {
            throw new InvalidArgumentException('"changelog_ids" is not provided in context');
        }

        return $context['changelog_ids'];
    }
}
