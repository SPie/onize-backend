<?php

use App\Exceptions\InvalidParameterException;
use App\Exceptions\ModelNotFoundException;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\User\UsersService;
use App\Services\User\UsersServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;
use LaravelDoctrine\Migrations\Testing\DatabaseMigrations;
use Mockery\MockInterface;
use Test\ModelHelper;
use Test\UserHelper;

/**
 * Class UserServiceTest
 */
class UsersServiceTest extends TestCase
{

    use DatabaseMigrations;
    use ModelHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function testGetUser(): void
    {
        $user = $this->createUsers()->first();

        $this->assertEquals($user, $this->createUserService()->getUser($user->getId()));
    }

    /**
     * @return void
     */
    public function testGetUserWithInvalidUserId(): void
    {
        try {
            $this->createUserService()->getUser($this->getFaker()->numberBetween());

            $this->assertTrue(false);
        } catch (ModelNotFoundException $e) {
            $this->assertEquals(UserModelInterface::class, $e->getModelClass());
        }
    }

    /**
     * @return void
     */
    public function testCreateUser(): void
    {
        $userData = [
            UserModelInterface::PROPERTY_EMAIL => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
        ];

        $user = $this->createUserService()->createUser($userData);

        $this->assertEquals($userData[UserModelInterface::PROPERTY_EMAIL], $user->getEmail());
        $this->assertTrue(Hash::check($userData[UserModelInterface::PROPERTY_PASSWORD], $user->getAuthPassword()));
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidData(): void
    {
        //missing email
        try {
            $this->createUserService()->createUser([
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //empty email
        try {
            $this->createUserService()->createUser([
                UserModelInterface::PROPERTY_EMAIL    => '',
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //missing password
        try {
            $this->createUserService()->createUser([
                UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //empty password
        try {
            $this->createUserService()->createUser([
                UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
                UserModelInterface::PROPERTY_PASSWORD => '',
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        //duplicated email
        try {
            $this->createUserService()->createUser([
                UserModelInterface::PROPERTY_EMAIL    => $this->createUsers()->first()->getEmail(),
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
            ]);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }
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

        $user = $this->createUserDoctrineModel();
        $editedUser = clone $user
            ->setEmail($userData[UserModelInterface::PROPERTY_EMAIL])
            ->setPassword($userData[UserModelInterface::PROPERTY_PASSWORD]);

        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('save')
            ->andReturn($editedUser);

        $userFactory = $this->createUserModelFactory();
        $userFactory
            ->shouldReceive('fill')
            ->andReturn($editedUser);

        $user = $this->createUserService(
            $userRepository,
            $userFactory
        )->editUser($user, $userData);

        $this->assertEquals($userData[UserModelInterface::PROPERTY_EMAIL], $user->getEmail());
        $this->assertTrue(Hash::check($userData[UserModelInterface::PROPERTY_PASSWORD], $user->getAuthPassword()));
    }

    /**
     * @return void
     */
    public function testEditUserWithoutChanges(): void
    {
        $user = $this->createUserDoctrineModel();

        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('save')
            ->andReturn($user);

        $userFactory = $this->createUserModelFactory();
        $userFactory
            ->shouldReceive('fill')
            ->andReturn($user);

        $this->assertEquals($user, $this->createUserService()->editUser($user, []));
    }

    /**
     * @return void
     */
    public function testEditUserWithInvalidData(): void
    {
        $user = $this->createUserDoctrineModel();

        $userFactory = $this->createUserModelFactory();
        $userFactory
            ->shouldReceive('fill')
            ->andThrow(new InvalidParameterException());

        $this->expectException(InvalidParameterException::class);

        $this->createUserService()->editUser(
            $user,
            [
                UserModelInterface::PROPERTY_EMAIL    => '',
                UserModelInterface::PROPERTY_PASSWORD => '',
            ]
        );
    }

    public function testEditUserWithExistingEmail(): void
    {
        $user = $this->createUserDoctrineModel();

        $userFactory = $this->createUserModelFactory();
        $userFactory
            ->shouldReceive('fill')
            ->andReturn($user);

        $userService = $this->createUserService(null, $userFactory);
        $userService
            ->shouldReceive('userExists')
            ->andReturn(true);

        $this->expectException(InvalidParameterException::class);

        $userService->editUser(
            $user,
            [
                UserModelInterface::PROPERTY_EMAIL => $this->getFaker()->safeEmail,
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
            ]
        );
    }

    //endregion

    /**
     * @param UserRepositoryInterface|null   $userRepository
     * @param UserModelFactoryInterface|null $userModelFactory
     *
     * @return UsersServiceInterface|MockInterface
     */
    private function createUserService(
        UserRepositoryInterface $userRepository = null,
        UserModelFactoryInterface $userModelFactory = null
    ): UsersServiceInterface
    {
        $userService = Mockery::spy(
            UsersService::class,
            [
                $userRepository ?: $this->getUserRepository(),
                $userModelFactory ?: $this->getUserModelFactory(),
            ]
        );

        return $userService
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }
}
