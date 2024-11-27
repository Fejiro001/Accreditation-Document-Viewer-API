<?php

namespace App\Http\Controllers;

use Auth;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    private const PROVIDER = 'google';
    private const TOKEN_NAME = 'Google';

    public function redirectToProvider()
    {
        return Socialite::driver(self::PROVIDER)
            ->scopes(['openid', 'profile', 'email'])->with(['access_type' => 'offline'])
            ->stateless()
            ->redirect();
    }

    public function handleProviderCallback()
    {
        try {
            $googleUser = Socialite::driver(self::PROVIDER)->stateless()->user();

            \Log::info('Google User:', (array) $googleUser);

            $user = User::updateOrCreate([
                'provider' => self::PROVIDER,
                'email' => $googleUser->getEmail(),
                'provider_id' => $googleUser->getId(),
            ], [
                'name' => $googleUser->getName(),
                'token' => $googleUser->token,
                'refresh_token' => $googleUser->refreshToken,
            ]);

            Auth::login($user, true);

            $token = $user->createToken('authToken')->accessToken;

            \Log::info('Generated token: ' . $token);

            $cookie = cookie('authToken', $token, 60 * 24, null, 'localhost', false, true, false, 'None');

            return redirect('http://localhost:5173')->withCookie($cookie);
        } catch (\Exception $e) {
            \Log::error('Google login failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to login with Google.'], 400);
        }
    }

    public function getUserName()
    {
        $user = Auth::user();
        \Log::info("User info: " . json_encode($user)); 
        return response()->json([
            'name' => $user->name,
        ]);
    }

    public function logout()
    {
        $user = Auth::user();

        if ($user) {
            $user->token()->revoke();
            Auth::logout();

            return response()->json(['message' => 'Successfully logged out']);

        }

        return response()->json(['message' => 'No user logged in'], 400);
    }
}
