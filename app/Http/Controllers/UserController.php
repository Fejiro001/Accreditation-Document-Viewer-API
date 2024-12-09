<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::with('folders')->get();
            return response()->json($users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ];
            }));
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'An error occurred while fetching the users'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|unique:users,email',
                'role' => 'required|in:admin,user',
                'permissions' => 'array',
                'permissions.*.folderId' => 'required|string',
                'permissions.*.hasAccess' => 'required|boolean'
            ]);

            $user = User::updateOrCreate(
                ['email' => $validated['email']],
                ['role' => $validated['role']]
            );

            foreach ($validated['permissions'] as $permission) {
                $folder = Folder::firstOrCreate([
                    'google_drive_id' => $permission['folderId']
                ]);

                $user->folders()->attach(
                    $folder->id,
                    [
                        'has_access' => $permission['hasAccess']
                    ]
                );
            }

            return response()->json([
                'message' => 'User added successfully',
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $exception->errors()
            ], 422);
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'An error occured while creating the user'
            ], 500);
        }
    }

    public function show(User $user)
    {
        try {
            $user->load('folders');

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'permissions' => $user->folders->map(function ($folder) {
                    return [
                        'folderId' => $folder->google_drive_id,
                        'hasAccess' => $folder->pivot->has_access
                    ];
                })
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'An error occurred while fetching the users info'
            ], 500);
        }
    }

    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|unique:users,email,' . $user->id,
                'role' => 'required|in:admin,user',
                'permissions' => 'array',
                'permissions.*.folderId' => 'required|string',
                'permissions.*.hasAccess' => 'required|boolean'
            ]);

            $user->update(
                [
                    'email' => $validated['email'],
                    'role' => $validated['role']
                ]
            );

            // Clear existing permissions
            $user->folders()->detach();

            foreach ($validated['permissions'] as $permission) {
                $folder = Folder::firstOrCreate([
                    'google_drive_id' => $permission['folderId']
                ]);

                $user->folders()->attach(
                    $folder->id,
                    [
                        'has_access' => $permission['hasAccess']
                    ]
                );
            }

            return response()->json([
                'message' => 'User updated successfully',
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $exception->errors()
            ], 422);
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'An error occured while updating the user'
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->folders()->detach();
            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully'
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'An error occurred while deleting the user'
            ], 500);
        }
    }
}
