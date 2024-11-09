<?php

namespace App\Services;

use Exception;
use Google\Client;
use Google\Service\Drive;

// Manages the Google Drive API Integration
class GoogleDriveService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        $this->client->addScope(Drive::DRIVE_READONLY);
    }

    public function authenticate($authCode)
    {
        try {
            // Exchanges authorization code for an access token
            $token = $this->client->fetchAccessTokenWithAuthCode($authCode);

            if (isset($token['error'])) {
                throw new Exception('Error fetching access token:' . $token['error_description']);
            }

            $this->client->setAccessToken($token);
            return $token;
        } catch (Exception $exception) {
            throw new Exception('Authentication failed: ' . $exception->getMessage());
        }

    }

    public function getDriveService()
    {
        return new Drive($this->client);
    }

    public function listFolderContent($folderId)
    {
        try {
            $driveService = $this->getDriveService();
            $query = "'{$folderId}' in parents";

            $response = $driveService->files->listFiles(['q' => $query]);
            return $response->getFiles();
        } catch (Exception $exception) {
            throw new Exception('Unable to list folder contents: ' . $exception->getMessage());
        }
    }

}