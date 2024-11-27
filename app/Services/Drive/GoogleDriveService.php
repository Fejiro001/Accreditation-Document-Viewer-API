<?php

namespace App\Services\Drive;

use Log;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use App\DataTransferObjects\FileData;
use App\Contracts\DriveServiceInterface;
use App\Exceptions\DriveServiceException;

class GoogleDriveService implements DriveServiceInterface
{
    private Drive $service;
    private Client $client;

    public function __construct(private readonly GoogleDriveConfig $config)
    {
        $this->initializeClient();
        $this->initializeDriveService();
    }

    private function initializeClient()
    {
        try {
            $this->client = new Client();
            $this->client->setAuthConfig($this->config->getServiceAccountPath());
            $this->client->setAccessType("offline");
            $this->client->addScope(Drive::DRIVE_READONLY);
            $this->client->setApplicationName($this->config->getApplicationName());
        } catch (\Throwable $e) {
            throw new DriveServiceException(
                "Failed to initialize Google Drive Client: {$e->getMessage()}",
                500,
                $e
            );
        }
    }

    private function initializeDriveService()
    {
        $this->service = new Drive($this->client);
    }

    public function listFolders(string $parentFolderId, int $depth = 2): array
    {
        if ($depth <= 0) {
            return [];
        }

        try {
            $files = $this->service->files->listFiles([
                'q' => "'{$parentFolderId}' in parents and trashed = false",
                'fields' => 'nextPageToken, files(id, name, mimeType)',
            ]);

            return array_map(
                fn(DriveFile $file) => $this->processFile($file, $depth),
                $files->getFiles()
            );
        } catch (\Throwable $e) {
            Log::error("Drive listing error: {$e->getMessage()}");
            throw new DriveServiceException("Unable to list folders: {$e->getMessage()}", 500, $e);
        }
    }

    private function processFile(DriveFile $file, int $depth)
    {
        $isFolder = $file->getMimeType() === 'application/vnd.google-apps.folder';
        $mimeType = $file->getMimeType();

        $subFolders = $isFolder && $depth > 1
            ? $this->listFolders($file->getId(), $depth - 1)
            : [];

        $content = null;
        $viewUrl = null;

        if (!$isFolder) {
            if ($mimeType === 'text/plain') {
                $content = $this->getFileContent($file->getId());
            } else {
                $viewUrl = $this->generateViewUrl($file->getId(), $mimeType);
            }
        }

        return new FileData(
            id: $file->getId(),
            name: $file->getName(),
            mimeType: $file->getMimeType(),
            isFolder: $isFolder,
            viewUrl: $viewUrl,
            content: $content,
            subFolders: $subFolders,
        );
    }

    public function generateViewUrl(string $fileId, string $mimeType): string
    {
        $baseViewUrl = "https://drive.google.com/file/d/{$fileId}/view";

        $viewerMapping = [
            'application/pdf' => "https://docs.google.com/viewer?url=https://drive.google.com/uc?id={$fileId}",
            'application/vnd.google-apps.document' => "https://docs.google.com/document/d/{$fileId}/view",
            'application/vnd.google-apps.spreadsheet' => "https://docs.google.com/spreadsheets/d/{$fileId}/view",
            'application/vnd.google-apps.presentation' => "https://docs.google.com/presentation/d/{$fileId}/view",
            'image/' => $baseViewUrl,
        ];

        foreach ($viewerMapping as $type => $url) {
            if (str_contains($mimeType, $type)) {
                return $url;
            }
        }
        return $baseViewUrl;
    }

    public function getFileContent(string $fileId): string
    {
        try {
            $response = $this->service->files->get($fileId, ['alt' => "media"]);
            return $response->getBody()->getContents();
        } catch (\Throwable $e) {
            throw new DriveServiceException(
                "Unable to retrieve file content: {$e->getMessage()}",
                500,
                $e
            );
        }
    }
}