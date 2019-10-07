<?php

namespace App\Services\Security;

use App\Models\User\LoginAttemptModel;
use App\Models\User\LoginAttemptModelFactory;
use App\Repositories\User\LoginAttemptRepository;

/**
 * Class LoginThrottlingService
 *
 * @package App\Services\Security
 */
final class LoginThrottlingService implements LoginThrottlingServiceInterface
{
    /**
     * @var LoginAttemptRepository
     */
    private $loginAttemptRepository;

    /**
     * @var LoginAttemptModelFactory
     */
    private $loginAttemptModelFactory;

    /**
     * @var int
     */
    private $maxLoginAttempts;

    /**
     * @var int
     */
    private $throttlingTimeInMinutes;

    /**
     * LoginThrottlingService constructor.
     *
     * @param LoginAttemptRepository   $loginAttemptRepository
     * @param LoginAttemptModelFactory $loginAttemptModelFactory
     * @param int                      $maxLoginAttempts
     * @param int                      $throttlingTimeInMinutes
     */
    public function __construct(
        LoginAttemptRepository $loginAttemptRepository,
        LoginAttemptModelFactory $loginAttemptModelFactory,
        int $maxLoginAttempts,
        int $throttlingTimeInMinutes
    ) {
        $this->loginAttemptRepository = $loginAttemptRepository;
        $this->loginAttemptModelFactory = $loginAttemptModelFactory;
        $this->maxLoginAttempts = $maxLoginAttempts;
        $this->throttlingTimeInMinutes = $throttlingTimeInMinutes;
    }

    /**
     * @return LoginAttemptRepository
     */
    private function getLoginAttemptRepository(): LoginAttemptRepository
    {
        return $this->loginAttemptRepository;
    }

    /**
     * @return LoginAttemptModelFactory
     */
    private function getLoginAttemptModelFactory(): LoginAttemptModelFactory
    {
        return $this->loginAttemptModelFactory;
    }

    /**
     * @return int
     */
    private function getMaxLoginAttempts(): int
    {
        return $this->maxLoginAttempts;
    }

    /**
     * @return int
     */
    private function getThrottlingTimeInMinutes(): int
    {
        return $this->throttlingTimeInMinutes;
    }

    /**
     * @param string $ipAddress
     * @param string $identifier
     * @param bool   $success
     *
     * @return LoginThrottlingService
     */
    public function logLoginAttempt(string $ipAddress, string $identifier, bool $success): LoginThrottlingServiceInterface
    {
        $this->getLoginAttemptRepository()->save(
            $this->getLoginAttemptModelFactory()->create(
                [
                    'ipAddress'   => $ipAddress,
                    'identifier'  => $identifier,
                    'attemptedAt' => new \DateTimeImmutable(),
                    'success'     => $success,
                ]
            )
        );

        return $this;
    }

    /**
     * @param string $ipAddress
     * @param string $identifier
     *
     * @return bool
     */
    public function isLoginBlocked(string $ipAddress, string $identifier): bool
    {
        $failedLoginAttempts = 0;

        $this->getLoginAttemptRepository()
            ->getLoginAttemptsForUserSince($ipAddress, $identifier, $this->createSinceDateTime())
            ->each(function (LoginAttemptModel $loginAttempt) use (&$failedLoginAttempts) {
                if ($loginAttempt->wasSuccess()) {
                    return false;
                }

                $failedLoginAttempts++;

                return true;
            });

        return $failedLoginAttempts >= $this->getMaxLoginAttempts();
    }

    /**
     * @return \DateTimeImmutable
     */
    private function createSinceDateTime()
    {
        return (new \DateTimeImmutable())->sub(
            new \DateInterval(\sprintf('PT%dM', $this->getThrottlingTimeInMinutes()))
        );
    }
}
