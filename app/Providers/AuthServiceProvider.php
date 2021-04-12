<?php

namespace App\Providers;

use App\Policies\UserPolicy;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [

//        User::class => UserPolicy::class,

    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes(function ($router) {
            $router->forAccessTokens();
        });

        Gate::define('update-post', function ($user, $post) {
            return $user->id == $post->user_id;
        });
//        Passport::tokensExpireIn(now()->addDays(15));
//
//        Passport::refreshTokensExpireIn(now()->addDays(30));
//
//        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        //
    }
}
