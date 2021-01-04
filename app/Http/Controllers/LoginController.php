<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\User;
use Debugbar;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function redirect (string $provider) {
        if ($response = $this->validateProvider($provider)) {
            return $response;
        }

        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback (string $provider) {
        if ($response = $this->validateProvider($provider)) {
            return $response;
        }

        try {
            /**
             * @var \Laravel\Socialite\Contracts\User
             */
            $user = Socialite::driver($provider)->stateless()->user();
        } catch (Exception $e) {
            Debugbar::addThrowable($e);
            return $this->error('Login failed.');
        }

        if (empty($user->getEmail())) {
            return $this->error('Please provide a email address.');
        }

        $provided = Provider::where([
            'provider' => $provider,
            'provided_id' => $user->getId()
        ])->first();
        if (isset($provided)) {
            $userCreated = $provided->user;
        } else {
            $userCreated = User::firstOrNew([
                'email' => $user->getEmail()
            ]);
            if ($userCreated->providers()
                ->where('provider', $provider)
                ->exists()) {
                return $this->error('This email address has already been used.');
            }

            Provider::create([
                'provider' => $provider,
                'provided_id' => $user->getId(),
                'user_id' => $userCreated->id,
                'avatar' => $user->getAvatar()
            ])->save();
        }

        $userCreated->forceFill([
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'email_verified_at' => now()
        ])->save();

        Auth::login($userCreated, true);

        return $this->success('Login successful.', data: $user);
    }

    protected function validateProvider (string $provider): Response | null {
        if (!in_array($provider, ['github'])) {
            return $this->error('Please login with GitHub.');
        } else return null;
    }
}
