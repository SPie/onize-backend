<?php

namespace App\Listeners\User;

use App\Exceptions\InvalidParameterException;
use App\Models\User\LoginAttemptModel;
use App\Models\User\LoginAttemptModelFactory;
use App\Repositories\User\LoginAttemptRepository;
use App\Services\Security\LoginThrottlingServiceInterface;
use SPie\LaravelJWT\Events\FailedLoginAttempt;

/**
 * Class LogFailedLoginAttempt
 *
 * @package App\Listeners\User
 */
final class LogFailedLoginAttempt
{
    /**
     * @var LoginThrottlingServiceInterface
     */
    private $loginThrottlingService;

    /**
     * LogFailedLoginAttempt constructor.
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
     * @param FailedLoginAttempt $failedLoginAttempt
     */
    public function handle(FailedLoginAttempt $failedLoginAttempt): void
    {
        $this->getLoginThrottlingService()->logLoginAttempt(
            $this->getIpAddressFromCredentials(
                $failedLoginAttempt->getCredentials()
            ),
            $this->getEmailFromCredentials(
                $failedLoginAttempt->getCredentials()
            ),
            false
        );
    }

    /**
     * @param array $credentials
     *
     * @return string
     *
     * @throws InvalidParameterException
     */
    private function getEmailFromCredentials(array $credentials): string
    {
        if (empty($credentials['email'])) {
            throw new InvalidParameterException();
        }

        return $credentials['email'];
    }

    /**
     * @param array $credentials
     *
     * @return string
     *
     * @throws InvalidParameterException
     */
    private function getIpAddressFromCredentials(array $credentials): string
    {
        if (empty($credentials['ipAddress'])) {
            throw new InvalidParameterException();
        }

        return $credentials['ipAddress'];
    }
}
