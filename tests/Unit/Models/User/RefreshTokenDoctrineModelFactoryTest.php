<?php

use App\Exceptions\InvalidParameterException;
use App\Models\User\RefreshTokenDoctrineModel;
use App\Models\User\RefreshTokenDoctrineModelFactory;
use App\Models\User\RefreshTokenModel;
use App\Models\User\RefreshTokenModelFactory;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use Test\AuthHelper;
use Test\UserHelper;

/**
 * Class RefreshTokenDoctrineModelFactoryTest
 */
class RefreshTokenDoctrineModelFactoryTest extends TestCase
{
    use AuthHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreate(): void
    {
        $data = [
            RefreshTokenModel::PROPERTY_ID          => $this->getFaker()->numberBetween(),
            RefreshTokenModel::PROPERTY_IDENTIFIER  => $this->getFaker()->uuid,
            RefreshTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->dateTime,
            RefreshTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
            RefreshTokenModel::PROPERTY_CREATED_AT  => $this->getFaker()->dateTime,
            RefreshTokenModel::PROPERTY_UPDATED_AT  => $this->getFaker()->dateTime,
        ];

        $this->assertEquals(
            (new RefreshTokenDoctrineModel(
                $data[RefreshTokenModel::PROPERTY_IDENTIFIER],
                $data[RefreshTokenModel::PROPERTY_USER],
                $data[RefreshTokenModel::PROPERTY_VALID_UNTIL],
                $data[RefreshTokenModel::PROPERTY_CREATED_AT],
                $data[RefreshTokenModel::PROPERTY_UPDATED_AT]
            ))->setId($data[RefreshTokenModel::PROPERTY_ID]),
            $this->createRefreshTokenDoctrineModelFactory()->create($data)
        );
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithRequiredParametersOnly(): void
    {
        $data = [
            RefreshTokenModel::PROPERTY_IDENTIFIER => $this->getFaker()->uuid,
            RefreshTokenModel::PROPERTY_USER       => $this->createUserDoctrineModel(),
        ];

        $this->assertEquals(
            (new RefreshTokenDoctrineModel(
                $data[RefreshTokenModel::PROPERTY_IDENTIFIER],
                $data[RefreshTokenModel::PROPERTY_USER]
            )),
            $this->createRefreshTokenDoctrineModelFactory()->create($data)
        );
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithUserData(): void
    {
        $user = $this->createUserDoctrineModel();

        $data = [
            RefreshTokenModel::PROPERTY_IDENTIFIER => $this->getFaker()->uuid,
            RefreshTokenModel::PROPERTY_USER       => [
                $this->getFaker()->uuid => $user->toArray(),
            ],
        ];

        $userModelFactory = $this->createUserModelFactory();
        $this->mockUserModelFactoryCreate($userModelFactory, $user, $data[RefreshTokenModel::PROPERTY_USER]);

        $this->assertEquals(
            (new RefreshTokenDoctrineModel(
                $data[RefreshTokenModel::PROPERTY_IDENTIFIER],
                $user
            )),
            $this->createRefreshTokenDoctrineModelFactory($userModelFactory)->create($data)
        );
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithoutUserData(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->createRefreshTokenDoctrineModelFactory()->create([
            RefreshTokenModel::PROPERTY_IDENTIFIER => $this->getFaker()->uuid,
            RefreshTokenModel::PROPERTY_USER       => $this->getFaker()->uuid,
        ]);
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithInvalidUserData(): void
    {
        $userModelFactory = $this->createUserModelFactory();
        $userModelFactory
            ->shouldReceive('create')
            ->andThrow(new InvalidParameterException());

        $this->expectException(InvalidParameterException::class);

        $this->createRefreshTokenDoctrineModelFactory($userModelFactory)->create([
            RefreshTokenModel::PROPERTY_IDENTIFIER => $this->getFaker()->uuid,
            RefreshTokenModel::PROPERTY_USER       => [
                $this->getFaker()->uuid => $this->getFaker()->uuid,
            ],
        ]);
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithEmptyIdentifier(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->createRefreshTokenDoctrineModelFactory()->create([
            RefreshTokenModel::PROPERTY_USER => $this->createUserDoctrineModel(),
        ]);
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithInvalidIdentifier(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->createRefreshTokenDoctrineModelFactory()->create([
            RefreshTokenModel::PROPERTY_IDENTIFIER => $this->getFaker()->numberBetween(),
            RefreshTokenModel::PROPERTY_USER       => $this->createUserDoctrineModel(),
        ]);
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithEmptyUser(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->createRefreshTokenDoctrineModelFactory()->create([
            RefreshTokenModel::PROPERTY_IDENTIFIER => $this->getFaker()->uuid,
        ]);
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithInvalidUser(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->createRefreshTokenDoctrineModelFactory()->create([
            RefreshTokenModel::PROPERTY_IDENTIFIER => $this->getFaker()->uuid,
            RefreshTokenModel::PROPERTY_USER       => $this->getFaker()->uuid,
        ]);
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithInvalidValidUntil(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->createRefreshTokenDoctrineModelFactory()->create([
            RefreshTokenModel::PROPERTY_IDENTIFIER  => $this->getFaker()->uuid,
            RefreshTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
            RefreshTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->uuid,
        ]);
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithInvalidCreatedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->createRefreshTokenDoctrineModelFactory()->create([
            RefreshTokenModel::PROPERTY_IDENTIFIER  => $this->getFaker()->uuid,
            RefreshTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
            RefreshTokenModel::PROPERTY_CREATED_AT  => $this->getFaker()->uuid,
        ]);
    }

    /**
     * @return void
     *
     * @throws InvalidParameterException
     */
    public function testCreateWithInvalidUpdatedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->createRefreshTokenDoctrineModelFactory()->create([
            RefreshTokenModel::PROPERTY_IDENTIFIER  => $this->getFaker()->uuid,
            RefreshTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
            RefreshTokenModel::PROPERTY_UPDATED_AT  => $this->getFaker()->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testFill(): void
    {
        $data = [
            RefreshTokenModel::PROPERTY_ID          => $this->getFaker()->numberBetween(),
            RefreshTokenModel::PROPERTY_IDENTIFIER  => $this->getFaker()->uuid,
            RefreshTokenModel::PROPERTY_VALID_UNTIL => $this->getFaker()->dateTime,
            RefreshTokenModel::PROPERTY_USER        => $this->createUserDoctrineModel(),
            RefreshTokenModel::PROPERTY_CREATED_AT  => $this->getFaker()->dateTime,
            RefreshTokenModel::PROPERTY_UPDATED_AT  => $this->getFaker()->dateTime,
        ];

        $this->assertEquals(
            (new RefreshTokenDoctrineModel(
                $data[RefreshTokenModel::PROPERTY_IDENTIFIER],
                $data[RefreshTokenModel::PROPERTY_USER],
                $data[RefreshTokenModel::PROPERTY_VALID_UNTIL],
                $data[RefreshTokenModel::PROPERTY_CREATED_AT],
                $data[RefreshTokenModel::PROPERTY_UPDATED_AT]
            ))->setId($data[RefreshTokenModel::PROPERTY_ID]),
            $this->createRefreshTokenDoctrineModelFactory()->fill($this->createRefreshTokenDoctrineModel(), $data)
        );
    }

    /**
     * @return void
     */
    public function testFillWithoutData(): void
    {
        $refreshToken = $this->createRefreshTokenDoctrineModel();

        $this->assertEquals(
            $refreshToken,
            $this->createRefreshTokenDoctrineModelFactory()->fill($refreshToken, [])
        );
    }

    //endregion

    //region Mocks

    /**
     * @param UserModelFactoryInterface|null $userModelFactory
     *
     * @return RefreshTokenDoctrineModelFactory|RefreshTokenModelFactory
     */
    private function createRefreshTokenDoctrineModelFactory(
        UserModelFactoryInterface $userModelFactory = null
    ): RefreshTokenDoctrineModelFactory {
        return (new RefreshTokenDoctrineModelFactory())->setUserModelFactory(
            $userModelFactory ?: $this->createUserModelFactory()
        );
    }

    /**
     * @param string|null             $identifier
     * @param UserModelInterface|null $user
     * @param DateTime|null           $validUntil
     *
     * @return RefreshTokenDoctrineModel
     */
    private function createRefreshTokenDoctrineModel(
        string $identifier = null,
        UserModelInterface $user = null,
        \DateTime $validUntil = null
    ): RefreshTokenDoctrineModel {
        return new RefreshTokenDoctrineModel(
            $identifier ?: $this->getFaker()->uuid,
            $user ?: $this->createUserDoctrineModel(),
            $validUntil ?: $this->getFaker()->dateTime
        );
    }

    //endregion
}
