<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            // Gets user from the provider
            $user = Socialite::driver($provider)->stateless()->user();

            $authUser = User::firstOrCreate([
                'email' => $user->getEmail(),
            ], [
                'name' => $user->getName(),
                'avatar' => $user->getAvatar(),
                'provider' => $provider,
                'provider_id' => $user->getId(),
                'token' => $user->token,
                'refreshToken' => $user->refreshToken,
            ]);

            Auth::login($authUser);

            return redirect()->to('/');
        } catch (\Exception $exception) {
            \Log::error("Google login error: " . $exception->getMessage());
        }

    }
}
