<?php

use App\Models\User\UserModelInterface;
use App\Repositories\DatabaseHandler;
use App\Repositories\User\UserDoctrineRepository;
use Mockery\MockInterface;
use Test\ModelHelper;
use Test\UserHelper;

/**
 * Class UserDoctrineRepositoryTest
 */
class UserDoctrineRepositoryTest extends TestCase
{
    use ModelHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testFindOneByEmail(): void
    {
        $email = $this->getFaker()->safeEmail;
        $user = $this->createUserDoctrineModel();
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, $user, ['email' => $email]);

        $this->assertEquals($user, $this->getUserRepository($databaseHandler)->findOneByEmail($email));
    }

    /**
     * @return void
     */
    public function testFindOneByEmailWithoutUser(): void
    {
        $email = $this->getFaker()->safeEmail;
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, null, ['email' => $email]);

        $this->assertEmpty($this->getUserRepository($databaseHandler)->findOneByEmail($email));
    }

    /**
     * @return void
     */
    public function testRetrieveById(): void
    {
        $email = $this->getFaker()->safeEmail;
        $user = $this->createUserDoctrineModel();
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, $user, ['email' => $email]);

        $this->assertEquals($user, $this->getUserRepository($databaseHandler)->retrieveById($email));
    }

    /**
     * @return void
     */
    public function testRetrieveByIdWithoutUser(): void
    {
        $email = $this->getFaker()->safeEmail;
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, null, ['email' => $email]);

        $this->assertEmpty($this->getUserRepository($databaseHandler)->retrieveById($this->getFaker()->safeEmail));
    }

    /**
     * @return void
     */
    public function testRetrieveByToken(): void
    {
        $email = $this->getFaker()->safeEmail;
        $user = $this->createUserDoctrineModel();
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, $user, ['email' => $email]);

        $this->assertEquals(
            $user,
            $this->getUserRepository($databaseHandler)->retrieveByToken($email, $this->getFaker()->uuid)
        );
    }

    /**
     * @return void
     */
    public function testRetrieveByTokenWithoutUser(): void
    {
        $email = $this->getFaker()->safeEmail;
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, null, ['email' => $email]);

        $this->assertEmpty($this->getUserRepository($databaseHandler)->retrieveByToken($email, $this->getFaker()->uuid));
    }

    /**
     * @return void
     */
    public function testUpdateRememberToken(): void
    {
        $this->assertEmpty(
            $this->getUserRepository()->updateRememberToken(
                $this->createUserModel(),
                $this->getFaker()->uuid
            )
        );
    }

    public function testRetrieveByCredentials(): void
    {
        $password = $this->getFaker()->password;
        $user = $this->createUserDoctrineModel(null, $password);
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, $user, ['email' => $user->getEmail()]);

        $this->assertEquals(
            $user,
            $this->getUserRepository($databaseHandler)->retrieveByCredentials([
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
            $this->getUserRepository()
                 ->retrieveByCredentials([UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password])
        );
    }

    public function testRetrieveByCredentialsWithoutPassword(): void
    {
        $this->assertEmpty(
            $this->getUserRepository()
                 ->retrieveByCredentials([UserModelInterface::PROPERTY_EMAIL => $this->getFaker()->safeEmail])
        );
    }

    public function testRetrieveByCredentialsWithInvalidPassword(): void
    {
        $user = $this->createUserDoctrineModel();
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, $user, ['email' => $user->getEmail()]);

        $this->assertEmpty(
            $this->getUserRepository($databaseHandler)->retrieveByCredentials([
                UserModelInterface::PROPERTY_EMAIL    => $user->getEmail(),
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
            ])
        );
    }

    public function testRetrieveByCredentialsWithoutUser(): void
    {
        $email = $this->getFaker()->safeEmail;
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, null, ['email' => $email]);

        $this->assertEmpty(
            $this->getUserRepository($databaseHandler)->retrieveByCredentials([
                UserModelInterface::PROPERTY_EMAIL    => $email,
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password,
            ])
        );
    }

    public function testValidateCredentials(): void
    {
        $password = $this->getFaker()->password;
        $user = $this->createUserDoctrineModel(null, $password);

        $this->assertTrue(
            $this->getUserRepository()->validateCredentials(
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
            $this->getUserRepository()->validateCredentials(
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
            $this->getUserRepository()->validateCredentials(
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
            $this->getUserRepository()->validateCredentials(
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
            $this->getUserRepository()->validateCredentials(
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
     * @param DatabaseHandler|null $databaseHandler
     *
     * @return UserDoctrineRepository
     */
    private function getUserRepository(DatabaseHandler $databaseHandler = null): UserDoctrineRepository
    {
        return new UserDoctrineRepository($databaseHandler ?: $this->createDatabaseHandler());
    }

    //endregion
}
