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
        return Socialite::driver($provider)->redirect();
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
                'provider_id' => $user->getId()
            ]);

            Auth::login($authUser);

            return redirect()->to('/dashboard');
        } catch (\Exception $exception) {
            return redirect('/login')->with('error', 'Login unsuccessful');
        }

    }
}
