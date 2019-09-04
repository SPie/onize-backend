<?php

namespace App\Providers;

use App\Listeners\User\LogFailedLoginAttempt;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use SPie\LaravelJWT\Events\FailedLoginAttempt;

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
        FailedLoginAttempt::class => [
            LogFailedLoginAttempt::class,
        ],
    ];
}
