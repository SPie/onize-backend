<?php

namespace Test;

use App\Models\Project\ProjectModel;
use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\User\UserRepository;
use App\Services\User\UsersService;
use App\Services\User\UsersServiceInterface;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;

/**
 * Trait UserHelper
 *
 * @package Test
 */
trait UserHelper
{

    /**
     * @param string|null $email
     * @param string|null $password
     *
     * @return UserDoctrineModel
     */
    protected function createUserDoctrineModel(string $email = null, string $password = null): UserDoctrineModel
    {
        return new UserDoctrineModel(
            $email ?: $this->getFaker()->safeEmail,
            $password ?: $this->getFaker()->password
        );
    }

    /**
     * @return UserModelInterface|MockInterface
     */
    protected function createUserModel(): UserModelInterface
    {
        return Mockery::spy(UserModelInterface::class);
    }

    /**
     * @param MockInterface $user
     * @param int|null      $id
     *
     * @return $this
     */
    protected function mockUserModelGetId(MockInterface $user, int $id = null)
    {
        $user
            ->shouldReceive('getId')
            ->andReturn($id);

        return $this;
    }

    /**
     * @param UserModelInterface|MockInterface $user
     * @param string                           $authIdentifier
     *
     * @return $this
     */
    protected function mockUserModelGetAuthIdentifier(MockInterface $user, string $authIdentifier)
    {
        $user
            ->shouldReceive('getAuthIdentifier')
            ->andReturn($authIdentifier);

        return $this;
    }

    /**
     * @param UserModelInterface|MockInterface $user
     * @param array                            $customClaims
     *
     * @return $this
     */
    protected function mockUserModelGetCustomClaims(MockInterface $user, array $customClaims)
    {
        $user
            ->shouldReceive('getCustomClaims')
            ->andReturn($customClaims);

        return $this;
    }

    /**
     * @param UserModelInterface|MockInterface $user
     * @param string                           $password
     *
     * @return $this
     */
    protected function mockUserModelGetAuthPassword(MockInterface $user, string $password)
    {
        $user
            ->shouldReceive('getAuthPassword')
            ->andReturn($password);

        return $this;
    }

    /**
     * @param UserModelInterface|MockInterface $user
     * @param ProjectModel[]|Collection        $projects
     *
     * @return $this
     */
    protected function mockUserModelGetProjects(MockInterface $user, Collection $projects)
    {
        $user
            ->shouldReceive('getProjects')
            ->andReturn($projects);

        return $this;
    }

    /**
     * @return UserModelFactoryInterface|Mockery\MockInterface
     */
    protected function createUserModelFactory(): UserModelFactoryInterface
    {
        return Mockery::spy(UserModelFactoryInterface::class);
    }

    /**
     * @param UserModelFactoryInterface|MockInterface $userModelFactory
     * @param UserModelInterface|\Exception|null      $user
     * @param array                                   $userData
     *
     * @return $this
     */
    protected function mockUserModelFactoryCreate(
        MockInterface $userModelFactory,
        $user = null,
        array $userData = []
    ) {
        $expectation = $userModelFactory
            ->shouldReceive('create')
            ->with($userData)
            ->andThrow($user);

        return $this;
    }

    /**
     * @return UserRepository|Mockery\MockInterface
     */
    protected function createUserRepository(): UserRepository
    {
        return Mockery::spy(UserRepository::class);
    }

    /**
     * @param UserRepository|MockInterface $userRepository
     * @param UserModelInterface|null      $user
     * @param string                       $email
     *
     * @return $this
     */
    protected function mockUserRepositoryFindOneByEmail(
        MockInterface $userRepository,
        ?UserModelInterface $user,
        string $email
    ) {
        $userRepository
            ->shouldReceive('findOneByEmail')
            ->with($email)
            ->andReturn($user);

        return $this;
    }

    /**
     * @return UsersServiceInterface|MockInterface
     */
    protected function createUsersService(): UsersServiceInterface
    {
        return Mockery::spy(UsersServiceInterface::class);
    }

    /**
     * @param UsersServiceInterface|MockInterface $usersService
     * @param UserModelInterface|\Exception       $user
     * @param int                                 $userId
     *
     * @return $this
     */
    protected function mockUsersServiceGetUser(MockInterface $usersService, $user, int $userId)
    {
        $usersService
            ->shouldReceive('getUser')
            ->with($userId)
            ->andThrow($user);

        return $this;
    }

    /**
     * @param UsersService|MockInterface    $usersService
     * @param UserModelInterface|\Exception $user
     * @param array                         $data
     *
     * @return $this
     */
    protected function mockUsersServiceCreateUser(MockInterface $usersService, $user, array $data)
    {
        $usersService
            ->shouldReceive('createUser')
            ->with($data)
            ->andThrow($user);

        return $this;
    }

    /**
     * @param UsersService|MockInterface    $usersService
     * @param UserModelInterface|\Exception $user
     * @param UserModelInterface            $inputUser
     * @param array                         $userData
     *
     * @return $this
     */
    protected function mockUsersServiceEditUser(
        MockInterface $usersService,
        $user,
        UserModelInterface $inputUser,
        array $userData
    ) {
        $usersService
            ->shouldReceive('editUser')
            ->with(
                Mockery::on(function ($argument) use ($inputUser) {
                    return $argument == $inputUser;
                }),
                $userData
            )
            ->andThrow($user);

        return $this;
    }

    /**
     * @param UsersServiceInterface|MockInterface $usersService
     * @param UserModelInterface|\Exception       $user
     * @param string                              $email
     *
     * @return $this
     */
    protected function mockUsersServiceGetUserByEmail(MockInterface $usersService, $user, string $email)
    {
        $usersService
            ->shouldReceive('getuserByEmail')
            ->with($email)
            ->andThrow($user);

        return $this;
    }

    /**
     * @param int    $times
     * @param array  $data
     *
     * @return UserModelInterface[]|Collection
     */
    protected function createUsers(int $times = 1, array $data = []): Collection
    {
        return $this->createModels(UserDoctrineModel::class, $times, $data);
    }

    /**
     * @return UserRepository
     */
    protected function getUserRepository(): UserRepository
    {
        return $this->app->get(UserRepository::class);
    }

    /**
     * @return UsersServiceInterface
     */
    protected function getUserService(): UsersServiceInterface
    {
        return $this->app->get(UsersServiceInterface::class);
    }

    /**
     * @return UserModelFactoryInterface
     */
    protected function getUserModelFactory(): UserModelFactoryInterface
    {
        return $this->app->get(UserModelFactoryInterface::class);
    }

    protected function createValidPassword(): string
    {
        return $this->getFaker()->password(8);
    }

    //region Assertions

    /**
     * @param UsersServiceInterface|MockInterface $usersService
     * @param string                              $email
     *
     * @return $this
     */
    protected function assertUsersServiceGetUserByEmail(MockInterface $usersService, string $email)
    {
        $usersService
            ->shouldHaveReceived('getUserByEmail')
            ->with($email)
            ->once();

        return $this;
    }

    /**
     * @param UsersServiceInterface|MockInterface $usersService
     * @param UserModelInterface                  $user
     * @param array                               $data
     *
     * @return $this
     */
    protected function assertUsersServiceEditUser(MockInterface $usersService, UserModelInterface $user, array $data)
    {
        $usersService
            ->shouldHaveReceived('editUser')
            ->with($user, $data)
            ->once();

        return $this;
    }

    //endregion
}
