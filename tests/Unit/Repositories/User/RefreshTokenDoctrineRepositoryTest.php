<?php

use App\Models\User\RefreshTokenModel;
use App\Repositories\DatabaseHandler;
use App\Repositories\User\RefreshTokenDoctrineRepository;
use Mockery\MockInterface;
use Test\AuthHelper;
use Test\ModelHelper;
use Test\UserHelper;

/**
 * Class RefreshTokenDoctrineRepositoryTest
 */
class RefreshTokenDoctrineRepositoryTest extends TestCase
{
    use AuthHelper;
    use ModelHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testFindOneByRefreshTokenId(): void
    {
        $refreshTokenId = $this->getFaker()->uuid;
        $refreshToken = $this->createRefreshToken();
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, $refreshToken, ['identifier' => $refreshTokenId]);

        $this->assertEquals(
            $refreshToken,
            $this->getrefreshtokenrepository($databaseHandler)->findOneByRefreshTokenId($refreshTokenId)
        );
    }

    /**
     * @return void
     */
    public function testFindOneByRefreshTokenIdWithoutRefreshToken(): void
    {
        $refreshTokenId = $this->getFaker()->uuid;
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, null, ['identifier' => $refreshTokenId]);

        $this->assertEmpty($this->getRefreshTokenRepository($databaseHandler)->findOneByRefreshTokenId($refreshTokenId));
    }

    //endregion

    //region Mocks

    /**
     * @param DatabaseHandler|null $databaseHandler
     *
     * @return RefreshTokenDoctrineRepository
     */
    private function getRefreshTokenRepository(DatabaseHandler $databaseHandler = null): RefreshTokenDoctrineRepository
    {
        return new RefreshTokenDoctrineRepository($databaseHandler ?: $this->createDatabaseHandler());
    }

    //endregion
}
