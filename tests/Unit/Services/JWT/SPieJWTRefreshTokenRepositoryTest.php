<?php

use App\Exceptions\InvalidParameterException;
use App\Exceptions\ModelNotFoundException;
use App\Models\User\RefreshTokenModelFactory;
use App\Repositories\User\RefreshTokenRepository;
use App\Repositories\User\UserRepository;
use App\Services\JWT\SPieJWTRefreshTokenRepository;
use Mockery\MockInterface;
use Test\AuthHelper;
use Test\UserHelper;

/**
 * Class SPieJWTRefreshTokenRepositoryTest
 */
class SPieJWTRefreshTokenRepositoryTest extends TestCase
{

    use AuthHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function testStoreRefreshToken(): void
    {
        $refreshToken = $this->createRefreshToken();
        $user = $this->createUserDoctrineModel();

        $refreshTokenModelFactory = $this->createRefreshTokenModelFactory();
        $refreshTokenModelFactory
            ->shouldReceive('create')
            ->andReturn($refreshToken);
        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('findOneByEmail')
            ->andReturn($user);
        $refreshTokenRepository = $this->createRefreshTokenRepository();
        $refreshTokenRepository
            ->shouldReceive('save');

        $jwtRefreshTokenRepository = $this->createSPieJWTRefreshTokenRepository(
            $refreshTokenModelFactory,
            $refreshTokenRepository,
            $userRepository
        );

        $jwt = $this->createJWT();

        $this->assertEquals($jwtRefreshTokenRepository, $jwtRefreshTokenRepository->storeRefreshToken($jwt));

        $refreshTokenModelFactory
            ->shouldHaveReceived('create')
            ->once();

        $refreshTokenRepository
            ->shouldHaveReceived('save')
            ->with(Mockery::on(function ($argument) use ($refreshToken) {
                return $argument == $refreshToken;
            }))
            ->once();
    }

    /**
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function testStoreRefreshTokenWithoutUser(): void
    {
        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('findOneByEmail')
            ->andReturnNull();

        $jwtRefreshTokenRepository = $this->createSPieJWTRefreshTokenRepository(
            null,
            null,
            $userRepository
        );

        $this->expectException(ModelNotFoundException::class);

        $jwtRefreshTokenRepository->storeRefreshToken($this->createJWT());
    }

    /**
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function testStoreRefreshTokenWithInvalidRefreshTokenData(): void
    {
        $refreshTokenModelFactory = $this->createRefreshTokenModelFactory();
        $refreshTokenModelFactory
            ->shouldReceive('create')
            ->andThrow(new InvalidParameterException());
        $userRepository = $this->createUserRepository();
        $userRepository
            ->shouldReceive('findOneByEmail')
            ->andReturn($this->createUserDoctrineModel());

        $jwtRefreshTokenRepository = $this->createSPieJWTRefreshTokenRepository(
            $refreshTokenModelFactory,
            null,
            $userRepository
        );

        $this->expectException(InvalidParameterException::class);

        $jwtRefreshTokenRepository->storeRefreshToken($this->createJWT());
    }

    /**
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function testRevokeRefreshToken(): void
    {
        $refreshTokenId = $this->getFaker()->uuid;
        $refreshToken = $this->createRefreshToken();

        $refreshTokenRepository = $this->createRefreshTokenRepository();
        $refreshTokenRepository
            ->shouldReceive('findOneByRefreshTokenId')
            ->andReturn($refreshToken);
        $refreshTokenRepository->shouldReceive('save');

        $jwtRefreshTokenRepository = $this->createSPieJWTRefreshTokenRepository(
            null,
            $refreshTokenRepository
        );

        $this->assertEquals($jwtRefreshTokenRepository, $jwtRefreshTokenRepository->revokeRefreshToken($refreshTokenId));

        $refreshTokenRepository
            ->shouldHaveReceived('findOneByRefreshTokenId')
            ->with($refreshTokenId)
            ->once();

        $refreshTokenRepository
            ->shouldHaveReceived('save')
            ->once();

        $refreshToken
            ->shouldHaveReceived('setValidUntil')
            ->with(Mockery::on(function ($argument) {
                $now = new \DateTimeImmutable();

                return (
                    $argument > $now->sub(new \DateInterval('PT5S'))
                    && $argument < $now->add(new \DateInterval('PT5S'))
                );
            }))
            ->once();
    }

    /**
     * @return void
     *
     * @throws ModelNotFoundException
     */
    public function testRevokeRefreshTokenWithoutRefreshToken(): void
    {
        $refreshTokenRepository = $this->createRefreshTokenRepository();
        $refreshTokenRepository
            ->shouldReceive('findOneByRefreshTokenId')
            ->andReturnNull();

        $jwtRefreshTokenRepository = $this->createSPieJWTRefreshTokenRepository(
            null,
            $refreshTokenRepository
        );

        $this->expectException(ModelNotFoundException::class);

        $jwtRefreshTokenRepository->revokeRefreshToken($this->getFaker()->uuid);
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testIsRefreshTokenRevoked(): void
    {
        $refreshToken = $this->createRefreshToken();
        $refreshToken
            ->shouldReceive('getValidUntil')
            ->andReturnNull();

        $refreshTokenRepository = $this->createRefreshTokenRepository();
        $refreshTokenRepository
            ->shouldReceive('findOneByRefreshTokenId')
            ->andReturn($refreshToken);

        $jwtRefreshTokenRepository = $this->createSPieJWTRefreshTokenRepository(
            null,
            $refreshTokenRepository
        );

        $this->assertFalse($jwtRefreshTokenRepository->isRefreshTokenRevoked($this->getFaker()->uuid));
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testIsRefreshTokenRevokedWithoutValidUntil(): void
    {
        $refreshToken = $this->createRefreshToken();
        $refreshToken
            ->shouldReceive('getValidUntil')
            ->andReturnNull();

        $refreshTokenRepository = $this->createRefreshTokenRepository();
        $refreshTokenRepository
            ->shouldReceive('findOneByRefreshTokenId')
            ->andReturn($refreshToken);

        $jwtRefreshTokenRepository = $this->createSPieJWTRefreshTokenRepository(
            null,
            $refreshTokenRepository
        );

        $this->assertFalse($jwtRefreshTokenRepository->isRefreshTokenRevoked($this->getFaker()->uuid));
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testIsRefreshTokenRevokedWithInvalidRefreshToken(): void
    {
        $refreshToken = $this->createRefreshToken();
        $refreshToken
            ->shouldReceive('getValidUntil')
            ->andReturn((new \DateTime())->sub(new \DateInterval('P1D')));

        $refreshTokenRepository = $this->createRefreshTokenRepository();
        $refreshTokenRepository
            ->shouldReceive('findOneByRefreshTokenId')
            ->andReturn($refreshToken);

        $jwtRefreshTokenRepository = $this->createSPieJWTRefreshTokenRepository(
            null,
            $refreshTokenRepository
        );

        $this->assertTrue($jwtRefreshTokenRepository->isRefreshTokenRevoked($this->getFaker()->uuid));
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testIsRefreshTokenRevokedWIthoutRefreshToken(): void
    {
        $refreshTokenRepository = $this->createRefreshTokenRepository();
        $refreshTokenRepository
            ->shouldReceive('findOneByRefreshTokenId')
            ->andReturnNull();

        $jwtRefreshTokenRepository = $this->createSPieJWTRefreshTokenRepository(
            null,
            $refreshTokenRepository
        );

        $this->assertTrue($jwtRefreshTokenRepository->isRefreshTokenRevoked($this->getFaker()->uuid));
    }

    //endregion

    //region Mocks

    /**
     * @param RefreshTokenModelFactory|null $refreshTokenModelFactory
     * @param RefreshTokenRepository|null   $refreshTokenRepository
     * @param UserRepository|null           $userRepository
     *
     * @return SPieJWTRefreshTokenRepository|MockInterface
     */
    private function createSPieJWTRefreshTokenRepository(
        RefreshTokenModelFactory $refreshTokenModelFactory = null,
        RefreshTokenRepository $refreshTokenRepository = null,
        UserRepository $userRepository = null
    ): SPieJWTRefreshTokenRepository
    {
        $spieJwtRefreshTokenRepository = Mockery::spy(
            SPieJWTRefreshTokenRepository::class,
            [
                $refreshTokenModelFactory ?: $this->createRefreshTokenModelFactory(),
                $refreshTokenRepository ?: $this->createRefreshTokenRepository(),
                $userRepository ?: $this->createUserRepository()
            ]
        );
        $spieJwtRefreshTokenRepository
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        return $spieJwtRefreshTokenRepository;
    }

    //endregion
}
