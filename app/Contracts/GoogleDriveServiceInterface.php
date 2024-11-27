<?php

namespace App\Contracts;

interface GoogleDriveServiceInterface
{
    public function listFolders(string $parentFolderId): array;
    public function getFileContent(string $fileId): string;
    public function getFile(string $fileId, array $options = []): object;
}