<?php

use App\Exceptions\InvalidParameterException;
use App\Exceptions\ModelNotFoundException;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\User\UserRepository;
use App\Services\JWT\JWTService;
use App\Services\User\UsersService;
use App\Services\User\UsersServiceInterface;
use Laravel\Lumen\Testing\TestCase;
use LaravelDoctrine\Migrations\Testing\DatabaseMigrations;
use Mockery\MockInterface;
use Test\ModelHelper;
use Test\ReflectionMethodHelper;
use Test\RepositoryHelper;
use Test\UserHelper;

/**
 * Class UserServiceTest
 */
class UsersServiceTest extends TestCase
{

    use DatabaseMigrations;
    use ModelHelper;
    use RepositoryHelper;
    use ReflectionMethodHelper;
    use TestCaseHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testGetUser(): void
    {
        $userId = $this->getFaker()->numberBetween();
        $user = $this->createUserModel();
        $userRepository = $this->createUserRepository();
        $this->mockRepositoryFind($userRepository, $user, $userId);

        $this->assertEquals($user, $this->createUserService($userRepository)->getUser($userId));
    }

    /**
     * @return void
     */
    public function testGetUserWithInvalidUserId(): void
    {
        $userId = $this->getFaker()->numberBetween();
        $userRepository = $this->createUserRepository();
        $this->mockRepositoryFind($userRepository, null, $userId);

        $this->expectException(ModelNotFoundException::class);

        $this->createUserService($userRepository)->getUser($userId);
    }

    /**
     * @return void
     */
    public function testGetUserByEmail(): void
    {
        $email = $this->getFaker()->safeEmail;
        $user = $this->createUserModel();
        $userRepository = $this->createUserRepository();
        $this->mockUserRepositoryFindOneByEmail($userRepository, $user, $email);

        $this->assertEquals($user, $this->createUserService($userRepository)->getUserByEmail($email));
    }

    /**
     * @return void
     */
    public function testGetUserByEmailWithoutUser(): void
    {
        $email = $this->getFaker()->safeEmail;
        $userRepository = $this->createUserRepository();
        $this->mockUserRepositoryFindOneByEmail($userRepository, null, $email);

        $this->expectException(ModelNotFoundException::class);

        $this->createUserService($userRepository)->getUserByEmail($email);
    }

    /**
     * @return void
     */
    public function testCreateUser(): void
    {
        $userData = [
            UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
        ];
        $user = $this->createUserModel();
        $userModelFactory = $this->createUserModelFactory();
        $this->mockModelFactoryCreate($userModelFactory, $user, $userData);
        $userRepository = $this->createUserRepository();
        $this->mockRepositorySave($userRepository, $user);
        $userService = $this->createUserService($userRepository, $userModelFactory);
        $this->mockUserServiceUserExists($userService, false, $user);

        $this->assertEquals($user, $userService->createUser($userData));
    }

    /**
     * @return void
     */
    public function testCreateUserWithExistingUser(): void
    {
        $userData = [
            UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
        ];
        $user = $this->createUserModel();
        $userModelFactory = $this->createUserModelFactory();
        $this->mockModelFactoryCreate($userModelFactory, $user, $userData);
        $userService = $this->createUserService(null, $userModelFactory);
        $this->mockUserServiceUserExists($userService, true, $user);

        $this->expectException(InvalidParameterException::class);

        $userService->createUser($userData);
    }

    /**
     * @return void
     */
    public function testEditUser(): void
    {
        $userData = [
            UserModelInterface::PROPERTY_EMAIL => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
        ];

        $userId = $this->getFaker()->numberBetween();
        $user = $this->createUserModel();
        $this->mockUserModelGetId($user, $userId);
        $userFactory = $this->createUserModelFactory();
        $this->mockModelFactoryFill($userFactory, $user, $userData, $user);
        $userRepository = $this->createUserRepository();
        $this->mockRepositorySave($userRepository, $user);
        $userService = $this->createUserService($userRepository, $userFactory);
        $this->mockUserServiceUserExists($userService, false, $user, $userId);

        $this->assertEquals($user, $userService->editUser($user, $userData));
    }

    /**
     * @return void
     */
    public function testEditUserWithExistingUser(): void
    {
        $userData = [
            UserModelInterface::PROPERTY_EMAIL => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
        ];

        $userId = $this->getFaker()->numberBetween();
        $user = $this->createUserModel();
        $this->mockUserModelGetId($user, $userId);
        $userFactory = $this->createUserModelFactory();
        $this->mockModelFactoryFill($userFactory, $user, $userData, $user);
        $userService = $this->createUserService(null, $userFactory);
        $this->mockUserServiceUserExists($userService, true, $user, $userId);

        $this->expectException(InvalidParameterException::class);

        $userService->editUser($user, $userData);
    }

    //endregion

    /**
     * @param UserRepository|null            $userRepository
     * @param UserModelFactoryInterface|null $userModelFactory
     * @param JWTService|null                $jwtService
     *
     * @return UsersServiceInterface|MockInterface
     */
    private function createUserService(
        UserRepository $userRepository = null,
        UserModelFactoryInterface $userModelFactory = null,
        JWTService $jwtService = null
    ): UsersServiceInterface
    {
        return Mockery::spy(
            UsersService::class,
            [
                $userRepository ?: $this->createUserRepository(),
                $userModelFactory ?: $this->createUserModelFactory(),
                $jwtService ?: $this->createJWTService()
            ]
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    /**
     * @param MockInterface $userService
     * @param bool          $response
     * @param MockInterface $user
     * @param int|null      $userId
     *
     * @return UsersServiceTest
     */
    private function mockUserServiceUserExists(
        MockInterface $userService,
        bool $response,
        MockInterface $user,
        int $userId = null
    ): UsersServiceTest
    {
        $arguments = [
            Mockery::on(function ($argument) use ($user) {
                return $argument == $user;
            })
        ];
        if ($userId !== null) {
            $arguments[] = $userId;
        }
        $userService
            ->shouldReceive('userExists')
            ->withArgs($arguments)
            ->andReturn($response);

        return $this;
    }
}
