<?php

namespace App\Services\Security;

/**
 * Interface LoginThrottlingServiceInterface
 *
 * @package App\Services\Security
 */
interface LoginThrottlingServiceInterface
{
    /**
     * @param string $ipAddress
     * @param string $identifier
     * @param bool   $success
     *
     * @return LoginThrottlingService
     */
    public function logLoginAttempt(string $ipAddress, string $identifier, bool $success): LoginThrottlingServiceInterface;

    /**
     * @param string $ipAddress
     * @param string $identifier
     *
     * @return bool
     */
    public function isLoginBlocked(string $ipAddress, string $identifier): bool;
}
