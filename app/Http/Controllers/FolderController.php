<?php

namespace App\Http\Controllers;

use App\Services\GoogleDriveService;
use Exception;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    public function addUser(Request $request, $folderId)
    {
        try {
            // Assume $request contains the ID of the user to add
            $userId = $request->input('user_id');

            // Your logic to add the user to the folder
            // Example: FolderUser model or service to handle associations

            // Return a success message
            return response()->json(['message' => 'User added to folder successfully.'], 200);


        } catch (Exception $exception) {
            // Error handling
            return response()->json(['error' => 'Unable to add user to folder.', 'details' => $exception->getMessage()], 500);

        }
    }

    public function viewFolder($folderId)
    {
        $files = $this->googleDriveService->listFolderContent($folderId);
        return response()->json($files);
    }
}
