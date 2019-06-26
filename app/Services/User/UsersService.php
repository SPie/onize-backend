<?php

namespace App\Services\User;

use App\Exceptions\InvalidParameterException;
use App\Exceptions\ModelNotFoundException;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\User\UserRepository;
use App\Services\JWT\JWTService;

/**
 * Class UsersService
 *
 * @package App\Services\User
 */
class UsersService implements UsersServiceInterface
{

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserModelFactoryInterface
     */
    private $userModelFactory;

    /**
     * @var JWTService
     */
    private $jwtService;

    /**
     * UsersService constructor.
     *
     * @param UserRepository            $userRepository
     * @param UserModelFactoryInterface $userModelFactory
     * @param JWTService                $jwtService
     */
    public function __construct(
        UserRepository $userRepository,
        UserModelFactoryInterface $userModelFactory,
        JWTService $jwtService
    )
    {
        $this->userRepository = $userRepository;
        $this->userModelFactory = $userModelFactory;
        $this->jwtService = $jwtService;
    }

    /**
     * @return UserRepository
     */
    protected function getUserRepository(): UserRepository
    {
        return $this->userRepository;
    }

    /**
     * @return UserModelFactoryInterface
     */
    protected function getUserModelFactory(): UserModelFactoryInterface
    {
        return $this->userModelFactory;
    }

    /**
     * @return JWTService
     */
    protected function getJWTService(): JWTService
    {
        return $this->jwtService;
    }

    /**
     * @param int $id
     *
     * @return UserModelInterface
     *
     * @throws ModelNotFoundException
     */
    public function getUser(int $id): UserModelInterface
    {
        $user = $this->getUserRepository()->find($id);
        if (!$user) {
            throw new ModelNotFoundException(UserModelInterface::class, $id);
        }

        return $user;
    }

    /**
     * @param string $email
     *
     * @return UserModelInterface
     *
     * @throws ModelNotFoundException
     */
    public function getUserByEmail(string $email): UserModelInterface
    {
        $user = $this->getUserRepository()->findOneByEmail($email);
        if (!$user) {
            throw new ModelNotFoundException(UserModelInterface::class, $email);
        }

        return $user;
    }

    /**
     * @param array $userData
     *
     * @return UserModelInterface
     *
     * @throws InvalidParameterException
     */
    public function createUser(array $userData): UserModelInterface
    {
        $user = $this->getUserModelFactory()->create($userData);
        if ($this->userExists($user)) {
            throw new InvalidParameterException();
        }

        return $this->getUserRepository()->save($user);
    }

    /**
     * @param UserModelInterface $user
     * @param array              $userData
     *
     * @return UserModelInterface
     *
     * @throws InvalidParameterException
     * @throws ModelNotFoundException
     */
    public function editUser(UserModelInterface $user, array $userData): UserModelInterface
    {
        $user = $this->getUserModelFactory()->fill($user, $userData);
        if ($this->userExists($user, $user->getId())) {
            throw new InvalidParameterException();
        }

        return $this->getUserRepository()->save($user);
    }

    /**
     * @param UserModelInterface $user
     * @param int|null           $userId
     *
     * @return bool
     */
    protected function userExists(UserModelInterface $user, int $userId = null): bool
    {
        $user = $this->getUserRepository()->findOneByEmail($user->getEmail());

        return ($user && $user->getId() != $userId);
    }
}
