<?php

namespace App\Listeners\User;

use App\Exceptions\InvalidParameterException;
use App\Services\Security\LoginThrottlingServiceInterface;
use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * LogSuccessfulLoginAttempt constructor.
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
     * @param Login $login
     */
    public function handle(Login $login): void
    {
        try {
            $this->getLoginThrottlingService()->logLoginAttempt(
                $this->getIpAddress($login),
                $this->getIdentifier($login),
                true
            );
        } catch (InvalidParameterException $e) {
            $this->getLogger()->warning('Could not log login attempt: ' . $e->getMessage());
        }
    }

    /**
     * @param Login $login
     *
     * @return string
     *
     * @throws InvalidParameterException
     */
    private function getIdentifier(Login $login): string
    {
        if (empty($login->getUser()->getAuthIdentifier())) {
            throw new InvalidParameterException('Missing identifier');
        }

        return $login->getUser()->getAuthIdentifier();
    }

    /**
     * @param Login $login
     *
     * @return string
     *
     * @throws InvalidParameterException
     */
    private function getIpAddress(Login $login): string
    {
        if (empty($login->getIpAddress())) {
            throw new InvalidParameterException('Missing ipAddress');
        }

        return $login->getIpAddress();
    }
}
