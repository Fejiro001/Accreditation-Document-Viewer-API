<?php
namespace App\DataTransferObjects;
use JsonSerializable;

class FileData implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $mimeType,
        public readonly bool $isFolder,
        public readonly ?string $viewUrl = null,
        public readonly ?string $content = null,
        public readonly array $subFolders = [],
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mimeType' => $this->mimeType,
            'isFolder' => $this->isFolder,
            'viewUrl' => $this->viewUrl,
            'content' => $this->content,
            'subFolders' => $this->subFolders
        ];
    }
}