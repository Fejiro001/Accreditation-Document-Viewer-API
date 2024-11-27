<?php

class FileData
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $mimeType,
        public readonly bool $isFolder,
        public readonly ?string $content = null,
        public readonly ?string $viewUrl = null,
        public readonly array $subFolders = [],
    ) {
    }

    public static function fromGoogleFile(object $file)
    {
        return new self(
            id: $file->getId(),
            name: $file->getName(),
            mimeType: $file->getMimeType(),
            isFolder: $file->getMimeType() === 'application/vnd.google-apps.folder',
        );
    }
}