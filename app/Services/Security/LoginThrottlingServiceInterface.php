<?php

namespace App\Services\Security;

/**
 * Interface LoginThrottlingServiceInterface
 *
 * @package App\Services\Security
 */
interface LoginThrottlingServiceInterface
{
    public function isLoginBlocked(string $ipAddress, string $identifier): bool;
}
