<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Integrations\Github;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('studentdb:read', function (User $user) {
            if ($user->tokenCan('studentdb:read')) {
                foreach ($user->providers as $provider) {
                    if ($provider->provider === 'github') {
                        /** @var Github $github */
                        $github = resolve(Github::class);

                        return $github->checkStudentDBAccess($provider->provided_id);
                    }
                }
            }

            return false;
        });

        Gate::define('user:token', function (User $user) {
            return $user->tokenCan('user:token');
        });

        Gate::define('admin', function (User $user) {
            return $user->is_admin && $user->tokenCan('admin');
        });
    }
}
