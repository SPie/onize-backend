<?php

namespace App\Listeners\User;

use App\Services\Security\LoginThrottlingServiceInterface;
use SPie\LaravelJWT\Events\Login;

/**
 * Class LogSuccessfulLoginAttempt
 *
 * @package App\Listeners\User
 */
final class LogSuccessfulLoginAttempt
{
    /**
     * @var LoginThrottlingServiceInterface
     */
    private $loginThrottlingService;

    /**
     * LogSuccessfulLoginAttempt constructor.
     *
     * @param LoginThrottlingServiceInterface $loginThrottlingService
     */
    public function __construct(LoginThrottlingServiceInterface $loginThrottlingService)
    {
        $this->loginThrottlingService = $loginThrottlingService;
    }

    /**
     * @return LoginThrottlingServiceInterface
     */
    private function getLoginThrottlingService(): LoginThrottlingServiceInterface
    {
        return $this->loginThrottlingService;
    }

    /**
     * @param Login $login
     */
    public function handle(Login $login): void
    {
        // TODO
    }
}
