<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserFolderController extends Controller
{
    public function store(Request $request, User $user)
    {
        $validated = $request->validate([
            'folder_id' => 'required|string'
        ]);

        $user->folders()->syncWithoutDetaching([$validated['folder_id']]);

        return response()->json(['message' => 'Folder access granted!']);
    }

    public function destroy(User $user, $folder_id)
    {
        $user->folders()->detach($folder_id);

        return response()->json(['message' => 'Folder access revoked!']);
    }
}
