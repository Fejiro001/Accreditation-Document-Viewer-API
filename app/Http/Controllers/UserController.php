<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('folders')->get();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email'
        ]);

        $user = User::create([
            'email' => $validated['email']
        ]);

        return response()->json([
            'message' => 'User added successfully',
            'user' => $user
        ]);
    }
}
