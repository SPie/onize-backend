<?php

namespace App\Providers;

use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Class AuthServiceProvider
 *
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('auth');
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->app->get('auth')->provider('app_user_provider', function ($app, array $config) {
            return $this->app->get(UserRepositoryInterface::class);
        });
    }
}
