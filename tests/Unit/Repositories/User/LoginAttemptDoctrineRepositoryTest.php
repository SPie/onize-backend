<?php

use App\Repositories\DatabaseHandler;
use App\Repositories\User\LoginAttemptDoctrineRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Test\ModelHelper;
use Test\RepositoryHelper;
use Test\UserHelper;

/**
 * Class LoginAttemptDoctrineRepositoryTest
 */
final class LoginAttemptDoctrineRepositoryTest extends TestCase
{
    use ModelHelper;
    use RepositoryHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testGetLoginAttemptsForUserSince(): void
    {
        $ipAddress = $this->getFaker()->ipv4;
        $identifier = $this->getFaker()->uuid;
        $since = new \DateTimeImmutable($this->getFaker()->dateTime->format('Y-m-d H:i:s'));
        $criteria = (new Criteria())
            ->where(new Comparison('ipAddress', '=', $ipAddress))
            ->andWhere(new Comparison('identifier', '=', $identifier))
            ->andWhere(new Comparison('attemptedAt', '>=', $since))
            ->orderBy(['id' => 'DESC']);
        $loginAttemptModel = $this->createLoginAttemptModel();
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoadByCriteria($databaseHandler, $this->createCollection([$loginAttemptModel]), $criteria);
        $loginAttemptDoctrineRepository = $this->getLoginAttemptDoctrineRepository($databaseHandler);

        $this->assertEquals(
            $this->createCollection([$loginAttemptModel]),
            $loginAttemptDoctrineRepository->getLoginAttemptsForUserSince($ipAddress, $identifier, $since)
        );
    }

    //endregion

    /**
     * @param DatabaseHandler $databaseHandler
     *
     * @return LoginAttemptDoctrineRepository
     */
    private function getLoginAttemptDoctrineRepository(DatabaseHandler $databaseHandler): LoginAttemptDoctrineRepository
    {
        return new LoginAttemptDoctrineRepository(
            $databaseHandler ?: $this->createDatabaseHandler()
        );
    }
}
