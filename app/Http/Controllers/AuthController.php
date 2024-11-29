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
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->stateless()
            ->redirect();
    }

    public function handleProviderCallback()
    {
        try {
            $googleUser = Socialite::driver(self::PROVIDER)->stateless()->user();

            $user = User::updateOrCreate(
                [
                    'provider' => self::PROVIDER,
                    'email' => $googleUser->getEmail(),
                    'provider_id' => $googleUser->getId(),
                ],
                [
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

    public function getUserName()
    {
        try {
            $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return response()->json([
                'name' => $user->name,
                'email' => $user->email,
                'status' => 'success'
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
