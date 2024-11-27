<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\DriveServiceInterface;
use App\Services\Drive\GoogleDriveConfig;
use App\Services\Drive\GoogleDriveService;

class GoogleDriveServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

        $this->app->bind(GoogleDriveConfig::class, fn() => new GoogleDriveConfig(
            serviceAccountPath: config('services.google_drive.service_account_path', storage_path('app/google-service-account.json')),
            applicationName: config('services.google_drive.application_name', 'Accreditation Document Viewer'),
            defaultFolderId: config('services.google_drive.default_folder_id', '1oOdXdyN1-_1HRGndMvphkz1M-NeIITyd')
        ));

        $this->app->bind(DriveServiceInterface::class, GoogleDriveService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
