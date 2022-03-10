<?php declare(strict_types=1);

namespace MateuszMesek\DocumentDataIndexerMview\Plugin\LimitNodePaths;

class State
{
    private array $locks = [];
    private array $documents = [];

    public function lock(string $documentName, array $documentIds, array $nodePaths): void
    {
        $this->locks[] = ['name' => $documentName, 'ids' => $documentIds, 'paths' => $nodePaths];
    }

    public function unlock(): void
    {
        array_unshift($this->locks);
    }

    public function setDocument(string $name, $id): void
    {
        $this->documents[] = ['name' => $name, 'id' => $id];
    }

    public function unsetDocument(): void
    {
        array_unshift($this->documents);
    }

    public function isLockedDocumentName(string $documentName): bool
    {
        $lock = current($this->locks);
        $document = current($this->documents);

        if (!$lock || !$document) {
            return false;
        }

        return ($lock['name'] === $document['name'])
            && ($document['name'] === $documentName);
    }

    public function isLockedPath(string $documentName, string $nodePath): bool
    {
        $lock = current($this->locks);
        $document = current($this->documents);

        if (!$lock || !$document) {
            return false;
        }

        return ($lock['name'] === $document['name'])
            && ($document['name'] === $documentName)
            && in_array($document['id'], $lock['ids'], false)
            && in_array($nodePath, $lock['paths'], true);
    }
}
