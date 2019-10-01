<?php

namespace App\Repositories\User;

use App\Models\User\LoginAttemptModel;
use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Interface LoginAttemptRepository
 *
 * @package App\Repositories\User
 */
interface LoginAttemptRepository extends RepositoryInterface
{
    /**
     * @param string             $ipAddress
     * @param string             $identifier
     * @param \DateTimeImmutable $since
     *
     * @return LoginAttemptModel[]|Collection
     */
    public function getLoginAttemptsForUserSince(
        string $ipAddress,
        string $identifier,
        \DateTimeImmutable $since
    ): Collection;
}
