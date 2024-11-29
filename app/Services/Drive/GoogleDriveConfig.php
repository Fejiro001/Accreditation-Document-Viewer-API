<?php

namespace App\Services\Drive;

class GoogleDriveConfig
{
    public function __construct(
        private readonly string $serviceAccountPath,
        private readonly string $applicationName,
        private readonly string $defaultFolderId,
    ) {
    }

    public function getServiceAccountPath(): string
    {
        return $this->serviceAccountPath;
    }

    public function getApplicationName(): string
    {
        return $this->applicationName;
    }

    public function getDefaultFolderId(): string
    {
        return $this->defaultFolderId;
    }
}