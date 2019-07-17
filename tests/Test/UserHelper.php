<?php

namespace Test;

use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\User\UserRepository;
use App\Services\JWT\JWTService;
use App\Services\User\UsersService;
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
    ) {
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
     * @param JWTService|MockInterface $jwtService
     * @param Response|\Exception      $response
     * @param UserModelInterface       $user
     * @param Response                 $inputResponse
     * @param bool                     $withRefreshToken
     *
     * @return $this
     */
    protected function mockJWTServiceIssueTokens(
        MockInterface $jwtService,
        $response,
        UserModelInterface $user,
        Response $inputResponse,
        bool $withRefreshToken
    ) {
        $jwtService
            ->shouldReceive('issueTokens')
            ->with(
                Mockery::on(function ($argument) use ($user) {
                    return $argument == $user;
                }),
                Mockery::on(function ($argument) use ($inputResponse) {
                    return $argument == $inputResponse;
                }),
                $withRefreshToken
            )
            ->andReturn($response);

        return $this;
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param Response|\Exception      $response
     * @param Response                 $inputResponse
     *
     * @return $this
     */
    protected function mockJWTServiceRefreshAccessToken(MockInterface $jwtService, $response, Response $inputResponse)
    {
        $jwtService
            ->shouldReceive('refreshAccessToken')
            ->with(Mockery::on(function ($argument) use ($inputResponse) {
                return $argument == $inputResponse;
            }))
            ->andThrow($response);

        return $this;
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param string                   $jwt
     * @param UserModelInterface       $user
     * @param int|null                 $ttl
     *
     * @return $this
     */
    protected function mockJWTServiceCreateJWT(
        MockInterface $jwtService,
        string $jwt,
        UserModelInterface $user,
        int $ttl = null
    ) {
        $arguments = [$user];
        if ($ttl !== null) {
            $arguments[] = $ttl;
        }
        $jwtService
            ->shouldReceive('createJWT')
            ->withArgs($arguments)
            ->andReturn($jwt);

        return $this;
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param string|\Exception        $subject
     * @param string                   $token
     *
     * @return $this
     */
    protected function mockJWTServiceVerifyJWT(MockInterface $jwtService, $subject, string $token)
    {
        $expectation = $jwtService
            ->shouldReceive('verifyJWT')
            ->with($token);

        if ($subject instanceof \Exception) {
            $expectation->andThrow($subject);

            return $this;
        }

        $expectation->andReturn($subject);

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
