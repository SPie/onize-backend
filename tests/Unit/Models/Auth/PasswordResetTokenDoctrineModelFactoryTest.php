<?php

use App\Exceptions\InvalidParameterException;
use App\Models\Auth\PasswordResetTokenDoctrineModel;
use App\Models\Auth\PasswordResetTokenDoctrineModelFactory;
use App\Models\Auth\PasswordResetTokenModel;
use App\Models\Auth\PasswordResetTokenModelFactory;
use App\Models\User\UserModelFactoryInterface;
use Test\AuthHelper;
use Test\UserHelper;

/**
 * Class PasswordResetTokenDoctrineModelFactoryTest
 */
final class PasswordResetTokenDoctrineModelFactoryTest extends IntegrationTestCase
{

    use AuthHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $data = [
            PasswordResetTokenModel::PROPERTY_ID          => $this->getFaker()->numberBetween(),
            PasswordResetTokenModel::PROPERTY_TOKEN       => $this->getFaker()->uuid,
            PasswordResetTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->dateTime,
            PasswordResetTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
            PasswordResetTokenModel::PROPERTY_CREATED_AT  => $this->getFaker()->dateTime,
            PasswordResetTokenModel::PROPERTY_UPDATED_AT  => $this->getFaker()->dateTime,
        ];

        $this->assertEquals(
            (new PasswordResetTokenDoctrineModel(
                $data[PasswordResetTokenModel::PROPERTY_TOKEN],
                $data[PasswordResetTokenModel::PROPERTY_VALID_UNTIL],
                $data[PasswordResetTokenModel::PROPERTY_USER],
                $data[PasswordResetTokenModel::PROPERTY_CREATED_AT],
                $data[PasswordResetTokenModel::PROPERTY_UPDATED_AT]
            ))->setId($data[PasswordResetTokenModel::PROPERTY_ID]),
            $this->createPasswordResetTokenModelFactory()->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithRequiredParametersOnly(): void
    {
        $data = [
            PasswordResetTokenModel::PROPERTY_TOKEN       => $this->getFaker()->uuid,
            PasswordResetTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->dateTime,
            PasswordResetTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
        ];

        $this->assertEquals(
            (new PasswordResetTokenDoctrineModel(
                $data[PasswordResetTokenModel::PROPERTY_TOKEN],
                $data[PasswordResetTokenModel::PROPERTY_VALID_UNTIL],
                $data[PasswordResetTokenModel::PROPERTY_USER]
            )),
            $this->createPasswordResetTokenModelFactory()->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithUserAsDataArray(): void
    {
        $user = $this->createUserDoctrineModel();
        $data = [
            PasswordResetTokenModel::PROPERTY_TOKEN       => $this->getFaker()->uuid,
            PasswordResetTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->dateTime,
            PasswordResetTokenModel::PROPERTY_USER        => $user->toArray(),
        ];
        $userModelFactory = $this->createUserModelFactory();
        $this->mockUserModelFactoryCreate($userModelFactory, $user, $data[PasswordResetTokenModel::PROPERTY_USER]);

        $this->assertEquals(
            (new PasswordResetTokenDoctrineModel(
                $data[PasswordResetTokenModel::PROPERTY_TOKEN],
                $data[PasswordResetTokenModel::PROPERTY_VALID_UNTIL],
                $user
            )),
            $this->createPasswordResetTokenModelFactory($userModelFactory)->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithoutToken(): void
    {

        $data = [
            PasswordResetTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->dateTime,
            PasswordResetTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
        ];

        $this->expectException(InvalidParameterException::class);

        $this->createPasswordResetTokenModelFactory()->create($data);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidTokenType(): void
    {
        $data = [
            PasswordResetTokenModel::PROPERTY_TOKEN       => $this->getFaker()->numberBetween(),
            PasswordResetTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->dateTime,
            PasswordResetTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
        ];

        $this->expectException(InvalidParameterException::class);

        $this->createPasswordResetTokenModelFactory()->create($data);
    }

    /**
     * @return void
     */
    public function testCreateWithoutValidUntil(): void
    {
        $data = [
            PasswordResetTokenModel::PROPERTY_TOKEN => $this->getFaker()->uuid,
            PasswordResetTokenModel::PROPERTY_USER  => $this->createUserDoctrineModel(),
        ];

        $this->expectException(InvalidParameterException::class);

        $this->createPasswordResetTokenModelFactory()->create($data);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidValidUntil(): void
    {
        $data = [
            PasswordResetTokenModel::PROPERTY_TOKEN       => $this->getFaker()->numberBetween(),
            PasswordResetTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->uuid,
            PasswordResetTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
        ];

        $this->expectException(InvalidParameterException::class);

        $this->createPasswordResetTokenModelFactory()->create($data);
    }

    /**
     * @return void
     */
    public function testCreateWithoutUser(): void
    {
        $data = [
            PasswordResetTokenModel::PROPERTY_TOKEN       => $this->getFaker()->numberBetween(),
            PasswordResetTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->dateTime,
        ];

        $this->expectException(InvalidParameterException::class);

        $this->createPasswordResetTokenModelFactory()->create($data);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidUserData(): void
    {
        $data = [
            PasswordResetTokenModel::PROPERTY_TOKEN       => $this->getFaker()->numberBetween(),
            PasswordResetTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->uuid,
            PasswordResetTokenModel::PROPERTY_USER        => [
                $this->getFaker()->uuid => $this->getFaker()->uuid
            ],
        ];

        $this->expectException(InvalidParameterException::class);

        $this->createPasswordResetTokenModelFactory()->create($data);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidCreatedAt(): void
    {
        $data = [
            PasswordResetTokenModel::PROPERTY_TOKEN       => $this->getFaker()->uuid,
            PasswordResetTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->dateTime,
            PasswordResetTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
            PasswordResetTokenModel::PROPERTY_CREATED_AT  => $this->getFaker()->uuid,
        ];

        $this->expectException(InvalidParameterException::class);

        $this->createPasswordResetTokenModelFactory()->create($data);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidUpdatedAt(): void
    {
        $data = [
            PasswordResetTokenModel::PROPERTY_TOKEN       => $this->getFaker()->uuid,
            PasswordResetTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->dateTime,
            PasswordResetTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
            PasswordResetTokenModel::PROPERTY_UPDATED_AT  => $this->getFaker()->uuid,
        ];

        $this->expectException(InvalidParameterException::class);

        $this->createPasswordResetTokenModelFactory()->create($data);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidId(): void
    {
        $data = [
            PasswordResetTokenModel::PROPERTY_TOKEN       => $this->getFaker()->uuid,
            PasswordResetTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->dateTime,
            PasswordResetTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
            PasswordResetTokenModel::PROPERTY_ID          => $this->getFaker()->uuid,
        ];

        $this->expectException(InvalidParameterException::class);

        $this->createPasswordResetTokenModelFactory()->create($data);
    }

    /**
     * @return void
     */
    public function testFill(): void
    {
        $data = [
            PasswordResetTokenModel::PROPERTY_ID          => $this->getFaker()->numberBetween(),
            PasswordResetTokenModel::PROPERTY_TOKEN       => $this->getFaker()->uuid,
            PasswordResetTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->dateTime,
            PasswordResetTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
            PasswordResetTokenModel::PROPERTY_CREATED_AT  => $this->getFaker()->dateTime,
            PasswordResetTokenModel::PROPERTY_UPDATED_AT  => $this->getFaker()->dateTime,
        ];
        $passwordResetToken = $this->createPasswordResetTokenDoctrineModel();

        $this->assertEquals(
            (new PasswordResetTokenDoctrineModel(
                $data[PasswordResetTokenModel::PROPERTY_TOKEN],
                $data[PasswordResetTokenModel::PROPERTY_VALID_UNTIL],
                $data[PasswordResetTokenModel::PROPERTY_USER],
                $data[PasswordResetTokenModel::PROPERTY_CREATED_AT],
                $data[PasswordResetTokenModel::PROPERTY_UPDATED_AT]
            ))->setId($data[PasswordResetTokenModel::PROPERTY_ID]),
            $this->createPasswordResetTokenModelFactory()->fill($passwordResetToken, $data)
        );
    }

    /**
     * @return void
     */
    public function testFillWithoutData(): void
    {
        $passwordResetToken = $this->createPasswordResetTokenDoctrineModel();

        $this->assertEquals(
            $passwordResetToken,
            $this->createPasswordResetTokenModelFactory()->fill(clone $passwordResetToken, [])
        );
    }

    //endregion

    //region Mocks

    /**
     * @param UserModelFactoryInterface|null $userModelFactory
     *
     * @return PasswordResetTokenDoctrineModelFactory|PasswordResetTokenModelFactory
     */
    private function createPasswordResetTokenModelFactory(
        UserModelFactoryInterface $userModelFactory = null
    ): PasswordResetTokenDoctrineModelFactory
    {
        return (new PasswordResetTokenDoctrineModelFactory())->setUserModelFactory(
            $userModelFactory ?: $this->createUserModelFactory()
        );
    }

    //endregion
}