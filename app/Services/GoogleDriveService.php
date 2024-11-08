<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;

class GoogleDriveService {
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(storage_path('app/google/credentials.json'));
        $this->client->addScope(Drive::DRIVE_METADATA_READONLY);
    }

    public function listFiles($folderId)
    {
        $driveService = new Drive($this->client);
        $query = "'{$folderId}' in parents";
        return $driveService->files->listFiles(['q' => $query])->getFiles();
    }
}