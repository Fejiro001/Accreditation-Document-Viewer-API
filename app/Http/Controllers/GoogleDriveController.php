<?php

namespace App\Http\Controllers;

use Cache;
use Exception;
use Google\Client;
use Google\Service\Drive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoogleDriveController extends Controller
{
    private $client;
    private $service;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(storage_path('app/google-service-account.json'));
        $this->client->setAccessType("offline");
        $this->client->addScope(Drive::DRIVE_READONLY);
        $this->client->setApplicationName('Accreditation Document Viewer');
        // $this->client->setHttpClient(new \GuzzleHttp\Client([
        //     'headers' => [
        //         'Accept-Encoding' => 'gzip'
        //     ]
        // ]));

        $this->service = new Drive($this->client);
    }

    /**
     * Processes a Google Drive file and returns its metadata.
     *
     * @param \Google\Service\Drive\DriveFile $file The Google Drive file to process.
     * @return array An associative array containing the file's metadata and content or view URL.
     */
    private function processFiles($file): array
    {
        $fileData = [
            'id' => $file->getId(),
            'name' => $file->getName(),
            'mimeType' => $file->getMimeType(),
            'isFolder' => $file->getMimeType() === 'application/vnd.google-apps.folder',
            'subFolders' => [],
        ];

        if ($fileData['mimeType'] === 'text/plain') {
            $fileContent = $this->service->files->get($file->id, ['alt' => 'media']);
            $fileData['content'] = $fileContent->getBody()->getContents();
        } elseif (!$fileData['isFolder']) {
            $fileData['viewUrl'] = $this->generateViewUrl(
                $file->getId(),
                $file->getMimeType()
            );
        }

        if ($fileData['isFolder']) {
            $fileData['subFolders'] = $this->listFolders($file->getId());
        }

        return $fileData;
    }

    public function listAllFolders($parentFolderId = null): JsonResponse
    {
        try {
            if (!$parentFolderId) {
                $parentFolderId = "1oOdXdyN1-_1HRGndMvphkz1M-NeIITyd";
            }

            $folderList = Cache::remember(
                "folders_{$parentFolderId}",
                now()->addMinutes(10),
                function () use ($parentFolderId) {
                    return $this->listFolders($parentFolderId);
                }
            );

            return response()->json($folderList, 200);
        } catch (Exception $e) {
            \Log::error('Error listing files from shared folder: ' . $e->getMessage());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function listFolders($parentFolderId): array
    {
        try {
            $folderList = [];
            $nextPageToken = null;

            do {
                $files = $this->service->files->listFiles([
                    'q' => "'{$parentFolderId}' in parents and trashed = false",
                    'fields' => 'nextPageToken, files(id, name, mimeType)',
                    'pageToken' => $nextPageToken,
                ]);


                foreach ($files->getFiles() as $file) {
                    $fileData = $this->processFiles($file);
                    $folderList[] = $fileData;
                }

                $nextPageToken = $files->getNextPageToken();
            } while ($nextPageToken !== null);

            return $folderList;
        } catch (Exception $e) {
            \Log::error('Error listing files in folder: ' . $e->getMessage());
            throw new Exception('Unable to list folders and files');
        }
    }

    public function generateViewUrl(string $fileId, string $mimeType)
    {
        $baseViewUrl = "https://drive.google.com/file/d/{$fileId}/view";
        $googleDocsViewerUrl = "https://docs.google.com/viewer";

        $viewerMapping = [
            'application/pdf' => "{$googleDocsViewerUrl}?url=",
            'application/vnd.google-apps.document' => "https://docs.google.com/document/d/{$fileId}/view",
            'application/vnd.google-apps.spreadsheet' => "https://docs.google.com/spreadsheets/d/{$fileId}/view",
            'application/vnd.google-apps.presentation' => "https://docs.google.com/presentation/d/{$fileId}/view",
            'image/' => $baseViewUrl, // Handles all image types (JPEG, PNG, etc.)
        ];

        foreach ($viewerMapping as $type => $url) {
            if (str_contains($mimeType, $type)) {
                if ($type === 'application/pdf') {
                    // Embed URL to use Google Docs Viewer
                    return "{$url}https://drive.google.com/uc?id={$fileId}";
                }
                return $url;
            }
        }

        return $baseViewUrl;
    }
}
