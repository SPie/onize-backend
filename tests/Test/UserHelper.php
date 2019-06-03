<?php

namespace Test;

use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\User\UserRepositoryInterface;
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
    )
    {
        $expectation = $userModelFactory
            ->shouldReceive('create')
            ->andThrow($user);

        return $this;
    }

    /**
     * @return UserRepositoryInterface|Mockery\MockInterface
     */
    protected function createUserRepository(): UserRepositoryInterface
    {
        return Mockery::spy(UserRepositoryInterface::class);
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
     * @return UserRepositoryInterface
     */
    protected function getUserRepository(): UserRepositoryInterface
    {
        return $this->app->get(UserRepositoryInterface::class);
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
}
