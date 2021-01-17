<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function index (Request $request) {
        /*return $this->error('Please login first.');*/

        // As GitHub is the only provider available now,
        // we can redirect this to `login/github`.
        return $this->redirect($request, 'github');
    }

    public function redirect (Request $request, string $provider) {
        if ($response = $this->validateProvider($provider)) {
            return $this->response($request, $response);
        }

        session([
            'login_from' => $request->input('from'),
        ]);

        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback (Request $request, string $provider) {
        if ($response = $this->validateProvider($provider)) {
            return $this->response($request, $response);
        }

        try {
            /**
             * @var \Laravel\Socialite\Contracts\User $user
             * @noinspection PhpPossiblePolymorphicInvocationInspection
             */
            $user = Socialite::driver($provider)->stateless()->user();
        } catch (Exception $e) {
            report($e);
            return $this->error('Login failed.');
        }

        if (empty($user->getEmail())) {
            return $this->error('Please provide a email address.');
        }

        $provided = Provider::where([
            'provider'    => $provider,
            'provided_id' => $user->getName()  // Actually GitHub user's login
        ])->first();

        if (!empty($provided)) {
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
        }

        $userCreated->forceFill([
            'email'             => $user->getEmail(),
            'name'              => $user->getName(),
            'email_verified_at' => now(),
        ])->save();

        // Make the first user admin.
        if ($userCreated->id === 1) {
            $userCreated->is_admin = true;
            $userCreated->save();
        }

        if (empty($provided)) {
            Provider::create([
                'provider'    => $provider,
                'provided_id' => $user->getNickname(),
                // Actually GitHub user's login
                'user_id'     => $userCreated->id,
                'avatar'      => $user->getAvatar(),
            ])->save();
        }

        Auth::login($userCreated, true);

        if ( ! $request->acceptsHtml()) {
            return $this->success('Login successful.', data: $user);
        }

        return redirect(session('login_from') ??
            route('home', absolute: false));
    }

    public function logout()
    {
        Auth::logout();

        return response(status: 204);
    }

    protected function validateProvider(string $provider) : ?string
    {
        if ( ! in_array($provider, ['github'])) {
            return 'Please login with GitHub.';
        } else {
            return null;
        }
    }

    protected function response(Request $request, ?string $message = null)
    {
        if ($request->acceptsHtml()) {
            return $message;
        }

        return $this->error($message);
    }
}
