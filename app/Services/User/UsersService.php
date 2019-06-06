<?php

namespace App\Services\User;

use App\Exceptions\InvalidParameterException;
use App\Exceptions\ModelNotFoundException;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UsersService
 *
 * @package App\Services\User
 */
class UsersService implements UsersServiceInterface
{

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var UserModelFactoryInterface
     */
    private $userModelFactory;

    /**
     * UsersService constructor.
     *
     * @param UserRepositoryInterface   $userRepository
     * @param UserModelFactoryInterface $userModelFactory
     */
    public function __construct(UserRepositoryInterface $userRepository, UserModelFactoryInterface $userModelFactory)
    {
        $this->userRepository = $userRepository;
        $this->userModelFactory = $userModelFactory;
    }

    /**
     * @return UserRepositoryInterface
     */
    protected function getUserRepository(): UserRepositoryInterface
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

    /**
     * @param Response $response
     * @param string   $email
     * @param string   $password
     * @param bool     $withRefreshToken
     *
     * @return Response
     */
    public function login(Response $response, string $email, string $password, bool $withRefreshToken = false): Response
    {
        // TODO: Implement login() method.
    }
}
