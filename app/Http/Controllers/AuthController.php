<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    private const PROVIDER = 'google';

    public function redirectToProvider()
    {
        return Socialite::driver(self::PROVIDER)
            ->scopes(['openid', 'profile', 'email'])
            ->with(['access_type' => 'offline'])
            ->stateless()
            ->redirect();
    }

    public function handleProviderCallback()
    {
        try {
            $googleUser = Socialite::driver(self::PROVIDER)->stateless()->user();

            $user = User::updateOrCreate(
                [
                    'email' => $googleUser->getEmail(),
                ],
                [
                    'provider' => self::PROVIDER,
                    'provider_id' => $googleUser->getId(),
                    'name' => $googleUser->getName(),
                    'token' => $googleUser->token,
                    'refresh_token' => $googleUser->refreshToken,
                ]
            );

            Auth::login($user, true);

            // Generate API token
            $token = $user->createToken('authToken')->accessToken;

            // Create a secure cookie with the token
            $cookie = cookie(
                'authToken',
                $token,
                60 * 24,
                '/',
                null,
                true,
                true,
                false,
                'Strict'
            );

            return redirect(config('app.frontend_url'))
                ->withCookie($cookie);
        } catch (\Exception $e) {
            \Log::error('Google login failed: ' . $e->getMessage());
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }

    public function getUserInfo()
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $displayName = $user->name ?? explode('@', $user->email)[0];

            return response()->json([
                'name' => $displayName,
                'email' => $user->email,
                'role' => $user->role,
                'status' => 'success',
                'permissions' => $user->folders->map(function ($folder) {
                    return [
                        'folderId' => $folder->google_drive_id,
                        'hasAccess' => $folder->pivot->has_access
                    ];
                })
            ], 200);
        } catch (\Exception $e) {
            \Log::error("Unauthorized access" . $e->getMessage());
            return response()->json(['error' => 'Failed fetch user data.'], 400);
        }
    }

    public function logout()
    {
        try {
            $user = Auth::guard('api')->user();
            if ($user) {
                $user->token()->revoke();
                Auth::guard('web')->logout();

                return response()->json(['message' => 'Successfully logged out']);
            }
        } catch (\Exception $e) {
            \Log::error('Logout failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to logout.'], 400);
        }
    }
}
