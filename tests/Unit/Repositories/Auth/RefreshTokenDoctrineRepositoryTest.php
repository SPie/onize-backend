<?php

use App\Models\Auth\RefreshTokenModel;
use App\Repositories\Auth\RefreshTokenDoctrineRepository;
use Mockery\MockInterface;
use Test\AuthHelper;
use Test\UserHelper;

/**
 * Class RefreshTokenDoctrineRepositoryTest
 */
class RefreshTokenDoctrineRepositoryTest extends TestCase
{

    use AuthHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testFindOneByRefreshTokenId(): void
    {
        $refreshTokenId = $this->getFaker()->uuid;
        $refreshToken = $this->createRefreshToken();

        $refreshTokenRepository = $this->createRefreshTokenRepository();
        $refreshTokenRepository
            ->shouldReceive('findOneBy')
            ->andReturn($refreshToken);

        $this->assertEquals($refreshToken, $refreshTokenRepository->findOneByRefreshTokenId($refreshTokenId));

        $refreshTokenRepository
            ->shouldHaveReceived('findOneBy')
            ->with([RefreshTokenModel::PROPERTY_IDENTIFIER => $refreshTokenId])
            ->once();
    }

    /**
     * @return void
     */
    public function testFindOneByRefreshTokenIdWithoutRefreshToken(): void
    {
        $refreshTokenRepository = $this->createRefreshTokenRepository();
        $refreshTokenRepository
            ->shouldReceive('findOneBy')
            ->andReturnNull();

        $this->assertEmpty($refreshTokenRepository->findOneByRefreshTokenId($this->getFaker()->uuid));
    }

    //endregion

    //region Mocks

    /**
     * @return RefreshTokenDoctrineRepository|MockInterface
     */
    private function createRefreshTokenRepository(): RefreshTokenDoctrineRepository
    {
        $refreshTokenRepository = Mockery::spy(RefreshTokenDoctrineRepository::class);
        $refreshTokenRepository
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        return $refreshTokenRepository;
    }

    //endregion
}
