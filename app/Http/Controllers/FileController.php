<?php

namespace App\Http\Controllers;

use App\Services\GoogleDriveService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService) {
        $this->googleDriveService = $googleDriveService;
    }

    public function listFiles(Request $request, $folderId) 
    {
        $files = $this->googleDriveService->listFiles($folderId);
        return response()->json($files);
    }
}
