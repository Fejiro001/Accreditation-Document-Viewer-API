<?php

namespace App\Http\Controllers;

use App\Contracts\DriveServiceInterface;
use App\Exceptions\DriveServiceException;
use App\Services\Drive\GoogleDriveConfig;
use Cache;
use Illuminate\Http\JsonResponse;
use Log;

class GoogleDriveController extends Controller
{
    public function __construct(
        private readonly DriveServiceInterface $driveService,
        private readonly GoogleDriveConfig $config
    ) {
    }

    /**
     * Lists all folders from Google Drive starting from a specified parent folder.
     *
     * @param string|null $parentFolderId
     * @param int $depth
     * @return \Illuminate\Http\JsonResponse
     */
    public function listAllFolders(?string $parentFolderId = null, int $depth = 2): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {

            $parentFolderId ??= $this->config->getDefaultFolderId();

            $folderList = Cache::remember(
                "folders_{$parentFolderId}_depth{$depth}",
                now()->addMinutes(30),
                fn() => $this->driveService->listFolders($parentFolderId, $depth)
            );

            return response()->json($folderList);
        } catch (DriveServiceException $e) {
            Log::error("Drive folder listing error: {$e->getMessage()}");
            return response()->json($e->toArray(), $e->getCode());
        }
    }
}
