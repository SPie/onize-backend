<?php

use App\Repositories\User\LoginAttemptDoctrineRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Test\RepositoryHelper;
use Test\UserHelper;

/**
 * Class LoginAttemptDoctrineRepositoryTest
 */
final class LoginAttemptDoctrineRepositoryTest extends TestCase
{
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
        $className = $this->getFaker()->uuid;
        $classMetaData = $this->createClassMetaData($className);
        $loginAttemptModel = $this->createLoginAttemptModel();
        $entityPersister = $this->createEntityPersister();
        $this->mockEntityPersisterLoadCriteria($entityPersister, [$loginAttemptModel], $criteria);
        $unitOfWork = $this->createUnitOfWork();
        $this->mockUnitOfWorkGetEntityPersister($unitOfWork, $entityPersister, $className);
        $entityManager = $this->createEntityManager();
        $this->mockEntityManagerGetUnitOfWork($entityManager, $unitOfWork);
        $loginAttemptDoctrineRepository = $this->getLoginAttemptDoctrineRepository($entityManager, $classMetaData);

        $this->assertEquals(
            $this->createCollection([$loginAttemptModel]),
            $loginAttemptDoctrineRepository->getLoginAttemptsForUserSince($ipAddress, $identifier, $since)
        );
    }

    //endregion

    /**
     * @param EntityManagerInterface|null $entityManager
     * @param ClassMetadata|null          $classMetadata
     *
     * @return LoginAttemptDoctrineRepository
     */
    private function getLoginAttemptDoctrineRepository(
        EntityManagerInterface $entityManager = null,
        ClassMetadata $classMetadata = null
    ): LoginAttemptDoctrineRepository {
        return new LoginAttemptDoctrineRepository(
            $entityManager ?: $this->createEntityManager(),
            $classMetadata ?: $this->createClassMetaData()
        );
    }
}
