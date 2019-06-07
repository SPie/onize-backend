<?php

namespace Test;

use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\JWT\JWTService;
use App\Services\User\UsersServiceInterface;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;

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
     * @return JWTService|MockInterface
     */
    protected function createJWTService(): JWTService
    {
        return Mockery::spy(JWTService::class);
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param Response|\Exception      $response
     * @param Response                 $inputResponse
     * @param array                    $credentials
     * @param bool|null                $withRefreshToken
     *
     * @return $this
     */
    protected function mockJWTServiceLogin(
        MockInterface $jwtService,
        $response,
        Response $inputResponse,
        array $credentials,
        bool $withRefreshToken = null
    )
    {
        $arguments = [
            Mockery::on(function ($argument) use ($inputResponse) {
                return $argument == $inputResponse;
            }),
            $credentials
        ];
        if ($withRefreshToken !== null) {
            $arguments[] = $withRefreshToken;
        }

        $jwtService
            ->shouldReceive('login')
            ->withArgs($arguments)
            ->andThrow($response);

        return $this;
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param Response                 $response
     *
     * @return $this
     */
    protected function mockJWTServiceLogout(MockInterface $jwtService, Response $response)
    {
        $jwtService
            ->shouldReceive('logout')
            ->with(Mockery::on(function ($argument) use ($response) {
                return $argument == $response;
            }))
            ->andReturn($response);

        return $this;
    }

    /**
     * @param JWTService|MockInterface      $jwtService
     * @param UserModelInterface|\Exception $user
     *
     * @return $this
     */
    protected function mockJWTServiceGetAuthenticatedUser(MockInterface $jwtService, $user)
    {
        $jwtService
            ->shouldReceive('getAuthenticatedUser')
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
