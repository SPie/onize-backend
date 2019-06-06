<?php

namespace App\Services\User;

use App\Exceptions\ModelNotFoundException;
use App\Models\User\UserModelInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @param Response $response
     * @param string   $email
     * @param string   $password
     * @param bool     $withRefreshToken
     *
     * @return Response
     */
    public function login(Response $response, string $email, string $password, bool $withRefreshToken = false): Response;
}
