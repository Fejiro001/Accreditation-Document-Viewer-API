<?php

namespace App\Contracts;

use App\DataTransferObjects\FileData;
use App\Exceptions\DriveServiceException;

interface DriveServiceInterface
{
    /**
     * List files and folders in a specific parent folder.
     * 
     * @param string $parentFolderId
     * @param int $depth Maximum recursion depth
     * @return FileData[]
     * @throws DriveServiceException
     */
    public function listFolders(string $parentFolderId, int $depth = 2): array;

    /**
     * Generate a view URL for a specific file.
     * 
     * @param string $fileId
     * @param string $mimeType
     * @return string
     */
    public function generateViewUrl(string $fileId, string $mimeType): string;

    /**
     * Retrieve file content for text files.
     * 
     * @param string $fileId
     * @return string
     * @throws DriveServiceException
     */
    public function getFileContent(string $fileId): string;
}