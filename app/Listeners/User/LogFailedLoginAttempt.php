<?php

namespace App\Listeners\User;

use App\Exceptions\InvalidParameterException;
use App\Services\Security\LoginThrottlingServiceInterface;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * LogFailedLoginAttempt constructor.
     *
     * @param LoginThrottlingServiceInterface $loginThrottlingService
     * @param LoggerInterface                 $logger
     */
    public function __construct(LoginThrottlingServiceInterface $loginThrottlingService, LoggerInterface $logger)
    {
        $this->loginThrottlingService = $loginThrottlingService;
        $this->logger = $logger;
    }

    /**
     * @return LoginThrottlingServiceInterface
     */
    private function getLoginThrottlingService(): LoginThrottlingServiceInterface
    {
        return $this->loginThrottlingService;
    }

    /**
     * @return LoggerInterface
     */
    private function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param FailedLoginAttempt $failedLoginAttempt
     */
    public function handle(FailedLoginAttempt $failedLoginAttempt): void
    {
        try {
            $this->getLoginThrottlingService()->logLoginAttempt(
                $this->getIpAddress($failedLoginAttempt),
                $this->getEmailFromCredentials(
                    $failedLoginAttempt->getCredentials()
                ),
                false
            );
        } catch (InvalidParameterException $e) {
            $this->getLogger()->warning('Could not log login attempt: ' . $e->getMessage());
        }
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
            throw new InvalidParameterException('Missing identifier');
        }

        return $credentials['email'];
    }

    /**
     * @param FailedLoginAttempt $failedLoginAttempt
     *
     * @return string
     *
     * @throws InvalidParameterException
     */
    private function getIpAddress(FailedLoginAttempt $failedLoginAttempt): string
    {
        if (empty($failedLoginAttempt->getIpAddress())) {
            throw new InvalidParameterException("Missing ipAddress");
        }

        return $failedLoginAttempt->getIpAddress();
    }
}
