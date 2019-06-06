<?php

use App\Models\User\UserModelInterface;
use App\Repositories\User\UserDoctrineRepository;
use Mockery\MockInterface;
use Test\UserHelper;

/**
 * Class UserDoctrineRepositoryTest
 */
class UserDoctrineRepositoryTest extends IntegrationTestCase
{

    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testFindOneByEmail(): void
    {
        $email = $this->getFaker()->safeEmail;
        $user = $this->createUserDoctrineModel();

        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('findOneBy')
            ->andReturn($user);

        $this->assertEquals($user, $userRepository->findOneByEmail($email));

        $userRepository
            ->shouldHaveReceived('findOneBy')
            ->with([UserModelInterface::PROPERTY_EMAIL => $email])
            ->once();
    }

    /**
     * @return void
     */
    public function testFindOneByEmailWithoutUser(): void
    {
        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('findOneBy')
            ->andReturnNull();

        $this->assertEmpty($userRepository->findOneByEmail($this->getFaker()->safeEmail));
    }

    /**
     * @return void
     */
    public function testRetrieveById(): void
    {
        $user = $this->createUserDoctrineModel();

        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('findOneByEmail')
            ->andReturn($user);

        $this->assertEquals($user, $userRepository->retrieveById($this->getFaker()->safeEmail));
    }

    /**
     * @return void
     */
    public function testRetrieveByIdWithoutUser(): void
    {
        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('findOneByEmail')
            ->andReturnNull();

        $this->assertEmpty($userRepository->retrieveById($this->getFaker()->safeEmail));
    }

    /**
     * @return void
     */
    public function testRetrieveByToken(): void
    {
        $user = $this->createUserDoctrineModel();

        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('findOneByEmail')
            ->andReturn($user);

        $this->assertEquals(
            $user,
            $userRepository->retrieveByToken($this->getFaker()->safeEmail, $this->getFaker()->uuid)
        );
    }

    /**
     * @return void
     */
    public function testRetrieveByTokenWithoutUser(): void
    {
        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('findOneByEmail')
            ->andReturnNull();

        $this->assertEmpty(
            $userRepository->retrieveByToken($this->getFaker()->safeEmail, $this->getFaker()->uuid)
        );
    }

    /**
     * @return void
     */
    public function testUpdateRememberToken(): void
    {
        $this->assertEmpty(
            $this->createUserRepository()->updateRememberToken(
                $this->createUserDoctrineModel(),
                $this->getFaker()->uuid
            )
        );
    }

    public function testRetrieveByCredentials(): void
    {
        $password = $this->getFaker()->password;
        $user = $this->createUserDoctrineModel(null, $password);

        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('findOneByEmail')
            ->andReturn($user);

        $this->assertEquals(
            $user,
            $userRepository->retrieveByCredentials([
                UserModelInterface::PROPERTY_EMAIL    => $user->getEmail(),
                UserModelInterface::PROPERTY_PASSWORD => $password,
            ])
        );
    }

    /**
     * @return void
     */
    public function testRetrieveByCredentialsWithoutEmail(): void
    {
        $this->assertEmpty(
            $this->createUserRepository()
                 ->retrieveByCredentials([UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password])
        );
    }

    public function testRetrieveByCredentialsWithoutPassword(): void
    {
        $this->assertEmpty(
            $this->createUserRepository()
                 ->retrieveByCredentials([UserModelInterface::PROPERTY_EMAIL => $this->getFaker()->safeEmail])
        );
    }

    public function testRetrieveByCredentialsWithInvalidPassword(): void
    {
        $user = $this->createUserDoctrineModel();

        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('findOneByEmail')
            ->andReturn($user);

        $this->assertEmpty(
            $userRepository->retrieveByCredentials([
                UserModelInterface::PROPERTY_EMAIL    => $user->getEmail(),
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
            ])
        );
    }

    public function testRetrieveByCredentialsWithoutUser(): void
    {
        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('findOneByEmail')
            ->andReturnNull();

        $this->assertEmpty(
            $userRepository->retrieveByCredentials([
                UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
            ])
        );
    }

    public function testValidateCredentials(): void
    {
        $password = $this->getFaker()->password;
        $user = $this->createUserDoctrineModel(null, $password);

        $this->assertTrue(
            $this->createUserRepository()->validateCredentials(
                $user,
                [
                    UserModelInterface::PROPERTY_EMAIL    => $user->getEmail(),
                    UserModelInterface::PROPERTY_PASSWORD => $password,
                ]
            )
        );
    }

    public function testValidateCredentialsWithInvalidPassword(): void
    {
        $user = $this->createUserDoctrineModel();

        $this->assertFalse(
            $this->createUserRepository()->validateCredentials(
                $user,
                [
                    UserModelInterface::PROPERTY_EMAIL    => $user->getEmail(),
                    UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
                ]
            )
        );
    }

    public function testValidateCredentialsWithInvalidEmail(): void
    {
        $password = $this->getFaker()->password;
        $user = $this->createUserDoctrineModel(null, $password);

        $this->assertFalse(
            $this->createUserRepository()->validateCredentials(
                $user,
                [
                    UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
                    UserModelInterface::PROPERTY_PASSWORD => $password,
                ]
            )
        );
    }

    public function testValidateCredentialsWithoutEmail(): void
    {
        $password = $this->getFaker()->password;

        $this->assertFalse(
            $this->createUserRepository()->validateCredentials(
                $this->createUserDoctrineModel(null, $password),
                [
                    UserModelInterface::PROPERTY_PASSWORD => $password,
                ]
            )
        );
    }

    public function testValidateCredentialsWithoutPassword(): void
    {
        $user = $this->createUserDoctrineModel();

        $this->assertFalse(
            $this->createUserRepository()->validateCredentials(
                $user,
                [
                    UserModelInterface::PROPERTY_EMAIL => $user->getEmail(),
                ]
            )
        );
    }

    //endregion

    //region Mocks

    /**
     * @return UserDoctrineRepository|MockInterface
     */
    private function createUserRepository(): UserDoctrineRepository
    {
        $userDoctrineRepository = Mockery::spy(UserDoctrineRepository::class);
        return $userDoctrineRepository
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    //endregion
}
