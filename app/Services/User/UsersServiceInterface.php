<?php

namespace App\Services\User;

use App\Exceptions\ModelNotFoundException;
use App\Models\User\UserModelInterface;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Interface UsersServiceInterface
 *
 * @package App\Services\User
 */
interface UsersServiceInterface
{

    /**
     * @param int $id
     *
     * @return UserModelInterface
     *
     * @throws ModelNotFoundException
     */
    public function getUser(int $id): UserModelInterface;

    /**
     * @param array $userData
     *
     * @return UserModelInterface
     */
    public function createUser(array $userData): UserModelInterface;

    /**
     * @param UserModelInterface $user
     * @param array              $userData
     *
     * @return UserModelInterface
     */
    public function editUser(UserModelInterface $user, array $userData): UserModelInterface;
}
