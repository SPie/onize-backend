<?php

use App\Exceptions\InvalidParameterException;
use App\Models\User\LoginAttemptDoctrineModel;
use App\Models\User\LoginAttemptDoctrineModelFactory;
use Test\UserHelper;

/**
 * Class LoginAttemptDoctrineModelFactoryTest
 */
final class LoginAttemptDoctrineModelFactoryTest extends TestCase
{
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $data = [
            'id'          => $this->getFaker()->numberBetween(),
            'ipAddress'   => $this->getFaker()->ipv4,
            'identifier'  => $this->getFaker()->uuid,
            'attemptedAt' => new \DateTimeImmutable($this->getFaker()->dateTime->format('Y-m-d H:i:s')),
            'success'     => $this->getFaker()->boolean,
        ];

        $this->assertEquals(
            (new LoginAttemptDoctrineModel(
                $data['ipAddress'],
                $data['identifier'],
                $data['attemptedAt'],
                $data['success']
            ))->setId($data['id']),
            $this->getLoginAttemptDoctrineModelFactory()->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithoutIpAddress(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getLoginAttemptDoctrineModelFactory()->create(
            [
                'identifier'  => $this->getFaker()->uuid,
                'attemptedAt' => $this->getFaker()->dateTime,
                'success'     => $this->getFaker()->boolean,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidIpAddress(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getLoginAttemptDoctrineModelFactory()->create(
            [
                'ipAddress'   => $this->getFaker()->numberBetween(),
                'identifier'  => $this->getFaker()->uuid,
                'attemptedAt' => $this->getFaker()->dateTime,
                'success'     => $this->getFaker()->boolean,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithoutIdentifier(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getLoginAttemptDoctrineModelFactory()->create(
            [
                'ipAddress'   => $this->getFaker()->ipv4,
                'attemptedAt' => $this->getFaker()->dateTime,
                'success'     => $this->getFaker()->boolean,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidIdentifier(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getLoginAttemptDoctrineModelFactory()->create(
            [
                'ipAddress'   => $this->getFaker()->ipv4,
                'identifier'  => $this->getFaker()->numberBetween(),
                'attemptedAt' => $this->getFaker()->dateTime,
                'success'     => $this->getFaker()->boolean,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithoutAttemptedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getLoginAttemptDoctrineModelFactory()->create(
            [
                'ipAddress'  => $this->getFaker()->ipv4,
                'identifier' => $this->getFaker()->uuid,
                'success'    => $this->getFaker()->boolean,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidAttemptedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getLoginAttemptDoctrineModelFactory()->create(
            [
                'ipAddress'   => $this->getFaker()->ipv4,
                'identifier'  => $this->getFaker()->uuid,
                'attemptedAt' => $this->getFaker()->word,
                'success'     => $this->getFaker()->boolean,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithoutSuccess(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getLoginAttemptDoctrineModelFactory()->create(
            [
                'ipAddress'   => $this->getFaker()->ipv4,
                'identifier'  => $this->getFaker()->uuid,
                'attemptedAt' => $this->getFaker()->dateTime,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidSuccess(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getLoginAttemptDoctrineModelFactory()->create(
            [
                'ipAddress'   => $this->getFaker()->ipv4,
                'identifier'  => $this->getFaker()->uuid,
                'attemptedAt' => $this->getFaker()->dateTime,
                'success'     => $this->getFaker()->uuid,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidId(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getLoginAttemptDoctrineModelFactory()->create(
            [
                'id'          => $this->getFaker()->word,
                'ipAddress'   => $this->getFaker()->ipv4,
                'identifier'  => $this->getFaker()->uuid,
                'attemptedAt' => $this->getFaker()->dateTime,
                'success'     => $this->getFaker()->boolean,
            ]
        );
    }

    /**
     * @return void
     */
    public function testFill(): void
    {
        $data = [
            'id' => $this->getFaker()->numberBetween(),
            'ipAddress' => $this->getFaker()->ipv4,
            'identifier' => $this->getFaker()->uuid,
            'attemptedAt' => new \DateTimeImmutable($this->getFaker()->dateTime->format('Y-m-d H:i:s')),
            'success' => $this->getFaker()->boolean,
        ];

        $this->assertEquals(
            (new LoginAttemptDoctrineModel(
                $data['ipAddress'],
                $data['identifier'],
                $data['attemptedAt'],
                $data['success']
            ))->setId($data['id']),
            $this->getLoginAttemptDoctrineModelFactory()->fill($this->createLoginAttemptDoctrineModel(), $data)
        );
    }

    /**
     * @return void
     */
    public function testFillWithoutData(): void
    {
        $loginAttemptModel = $this->createLoginAttemptDoctrineModel();

        $this->assertEquals(
            $loginAttemptModel,
            $this->getLoginAttemptDoctrineModelFactory()->fill($loginAttemptModel, [])
        );
    }

    //endregion

    /**
     * @return LoginAttemptDoctrineModelFactory
     */
    private function getLoginAttemptDoctrineModelFactory(): LoginAttemptDoctrineModelFactory
    {
        return new LoginAttemptDoctrineModelFactory();
    }
}
