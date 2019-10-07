<?php

namespace App\Providers;

use App\Listeners\User\LogFailedLoginAttempt;
use App\Listeners\User\LogSuccessfulLoginAttempt;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use SPie\LaravelJWT\Events\FailedLoginAttempt;
use SPie\LaravelJWT\Events\Login;

/**
 * Class EventServiceProvider
 *
 * @package App\Providers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Login::class              => [
            LogSuccessfulLoginAttempt::class,
        ],
        FailedLoginAttempt::class => [
            LogFailedLoginAttempt::class,
        ],
    ];
}
