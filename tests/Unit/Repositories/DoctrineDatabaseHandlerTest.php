<?php

use App\Models\ModelInterface;
use App\Repositories\DoctrineDatabaseHandler;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\UnitOfWork;
use Illuminate\Support\Collection;
use Mockery as m;
use Mockery\MockInterface;
use Test\ModelHelper;

/**
 * Class DoctrineDatabaseHandlerTest
 */
final class DoctrineDatabaseHandlerTest extends TestCase
{
    use ModelHelper;

    //region Tests

    /**
     * @return void
     */
    public function testFind(): void
    {
        $id = $this->getFaker()->numberBetween();
        $className = $this->getFaker()->uuid;
        $model = $this->createModel();
        $entityManager = $this->createEntityManager();
        $this->mockEntityManagerFind($entityManager, $model, $className, $id);

        $this->assertEquals($model, $this->getDoctrineDatabaseHandler($entityManager, $className)->find($id));
    }

    /**
     * @return void
     */
    public function testFindWithoutModel(): void
    {
        $id = $this->getFaker()->numberBetween();
        $className = $this->getFaker()->uuid;
        $entityManager = $this->createEntityManager();
        $this->mockEntityManagerFind($entityManager, null, $className, $id);

        $this->assertEmpty($this->getDoctrineDatabaseHandler($entityManager, $className)->find($id));
    }

    /**
     * @return void
     */
    public function testLoad(): void
    {
        $criteria = [$this->getFaker()->word => $this->getFaker()->word];
        $className = $this->getFaker()->uuid;
        $model = $this->createModel();
        $entityPersister = $this->createEntityPersister();
        $this->mockEntityPersisterLoad($entityPersister, $model, $criteria);
        $entityManager = $this->createEntityManagerWithEntityPersister($className, $entityPersister);

        $this->assertEquals(
            $model,
            $this->getDoctrineDatabaseHandler(
                $this->createEntityManagerWithEntityPersister($className, $entityPersister),
                $className
            )->load($criteria)
        );
    }

    /**
     * @return void
     */
    public function testLoadWithoutModel(): void
    {
        $criteria = [$this->getFaker()->word => $this->getFaker()->word];
        $className = $this->getFaker()->uuid;
        $entityPersister = $this->createEntityPersister();
        $this->mockEntityPersisterLoad($entityPersister, null, $criteria);

        $this->assertEmpty(
            $this->getDoctrineDatabaseHandler(
                $this->createEntityManagerWithEntityPersister($className, $entityPersister),
                $className
            )->load($criteria)
        );
    }

    /**
     * @return void
     */
    public function testLoadAll(): void
    {
        $className = $this->getFaker()->uuid;
        $criteria = [$this->getFaker()->word => $this->getFaker()->word];
        $orderBy = [$this->getFaker()->word => $this->getFaker()->word];
        $limit = $this->getFaker()->numberBetween();
        $offset = $this->getFaker()->numberBetween();
        $models = [$this->createModel()];
        $entityPersister = $this->createEntityPersister();
        $this->mockEntityPersisterLoadAll(
            $entityPersister,
            $models,
            $criteria,
            $orderBy,
            $limit,
            $offset
        );

        $this->assertEquals(
            new Collection($models),
            $this->getDoctrineDatabaseHandler(
                $this->createEntityManagerWithEntityPersister($className, $entityPersister),
                $className
            )->loadAll($criteria, $orderBy, $limit, $offset)
        );
    }

    /**
     * @return void
     */
    public function testLoadAllWithoutModels(): void
    {
        $className = $this->getFaker()->uuid;
        $criteria = [$this->getFaker()->word => $this->getFaker()->word];
        $orderBy = [$this->getFaker()->word => $this->getFaker()->word];
        $limit = $this->getFaker()->numberBetween();
        $offset = $this->getFaker()->numberBetween();
        $entityPersister = $this->createEntityPersister();
        $this->mockEntityPersisterLoadAll(
            $entityPersister,
            [],
            $criteria,
            $orderBy,
            $limit,
            $offset
        );

        $this->assertEquals(
            new Collection(),
            $this->getDoctrineDatabaseHandler(
                $this->createEntityManagerWithEntityPersister($className, $entityPersister),
                $className
            )->loadAll($criteria, $orderBy, $limit, $offset)
        );
    }

    /**
     * @return void
     */
    public function testLoadByCriteria(): void
    {
        $className = $this->getFaker()->uuid;
        $models = [$this->createModel()];
        $criteria = new Criteria();
        $entityPersister = $this->createEntityPersister();
        $this->mockEntityPersisterLoadCriteria($entityPersister, $models, $criteria);

        $this->assertEquals(
            new Collection($models),
            $this->getDoctrineDatabaseHandler(
                $this->createEntityManagerWithEntityPersister($className, $entityPersister),
                $className
            )->loadByCriteria($criteria)
        );
    }

    /**
     * @return void
     */
    public function testLoadByCriteriaWithoutModels(): void
    {
        $className = $this->getFaker()->uuid;
        $criteria = new Criteria();
        $entityPersister = $this->createEntityPersister();
        $this->mockEntityPersisterLoadCriteria($entityPersister, [], $criteria);

        $this->assertEquals(
            new Collection(),
            $this->getDoctrineDatabaseHandler(
                $this->createEntityManagerWithEntityPersister($className, $entityPersister),
                $className
            )->loadByCriteria($criteria)
        );
    }

    /**
     * @return void
     */
    public function testSaveWithFlush(): void
    {
        $model = $this->createModel();
        $entityManager = $this->createEntityManager();

        $this->assertEquals(
            $model,
            $this->getDoctrineDatabaseHandler($entityManager, $this->getFaker()->uuid)->save($model, true)
        );
        $this
            ->assertEntityManagerPersist($entityManager, $model)
            ->assertEntityManagerFlush($entityManager);
    }

    /**
     * @return void
     */
    public function testSaveWithoutFlush(): void
    {
        $model = $this->createModel();
        $entityManager = $this->createEntityManager();

        $this->getDoctrineDatabaseHandler($entityManager, $this->getFaker()->uuid)->save($model, false);

        $this->assertEntityManagerPersist($entityManager, $model);
        $entityManager->shouldNotHaveReceived('flush');
    }

    /**
     * @return void
     */
    public function testDeleteWithFlush(): void
    {
        $model = $this->createModel();
        $entityManager = $this->createEntityManager();
        $databaseHandler = $this->getDoctrineDatabaseHandler($entityManager, $this->getFaker()->uuid);

        $this->assertEquals($databaseHandler, $databaseHandler->delete($model, true));
        $this
            ->assertEntityManagerRemove($entityManager, $model)
            ->assertEntityManagerFlush($entityManager);
    }

    /**
     * @return void
     */
    public function testDeleteWithoutFlush(): void
    {
        $model = $this->createModel();
        $entityManager = $this->createEntityManager();

        $this->getDoctrineDatabaseHandler($entityManager, $this->getFaker()->uuid)->delete($model, false);

        $this->assertEntityManagerRemove($entityManager, $model);
        $entityManager->shouldNotHaveReceived('flush');
    }

    /**
     * @return void
     */
    public function testFlush(): void
    {
        $entityManager = $this->createEntityManager();
        $databaseHandler = $this->getDoctrineDatabaseHandler($entityManager, $this->getFaker()->uuid);

        $this->assertEquals($databaseHandler, $databaseHandler->flush());
        $this->assertEntityManagerFlush($entityManager);
    }

    //endregion

    /**
     * @param EntityManager|null $entityManager
     * @param string|null        $classname
     *
     * @return DoctrineDatabaseHandler
     */
    private function getDoctrineDatabaseHandler(
        EntityManager $entityManager = null,
        string $classname = null
    ): DoctrineDatabaseHandler {
        return new DoctrineDatabaseHandler(
            $entityManager ?: $this->createEntityManager(),
            $classname ?: $this->getFaker()->uuid
        );
    }

    /**
     * @return EntityManager|MockInterface
     */
    private function createEntityManager(): EntityManager
    {
        return m::spy(EntityManager::class);
    }

    private function createEntityManagerWithEntityPersister(
        string $classname = null,
        EntityPersister $entityPersister = null
    ): EntityManager {
        $entityPersister = $entityPersister ?: $this->createEntityPersister();
        $unitOfWork = $this->createUnitOfWork();
        $this->mockUnitOfWorkGetEntityPersister(
            $unitOfWork,
            $entityPersister,
            $classname ?: $this->getFaker()->uuid
        );
        $entityManager = $this->createEntityManager();
        $this->mockEntityManagerGetUnitOfWork($entityManager, $unitOfWork);

        return $entityManager;
    }

    /**
     * @param EntityManager|MockInterface $entityManager
     * @param UnitOfWork                  $unitOfWork
     *
     * @return $this
     */
    private function mockEntityManagerGetUnitOfWork(MockInterface $entityManager, UnitOfWork $unitOfWork): self
    {
        $entityManager
            ->shouldReceive('getUnitOfWork')
            ->andReturn($unitOfWork);

        return $this;
    }

    /**
     * @param EntityManager|MockInterface $entityManager
     * @param ModelInterface|null         $model
     * @param string                      $className
     * @param int                         $id
     *
     * @return $this
     */
    private function mockEntityManagerFind(
        MockInterface $entityManager,
        ?ModelInterface $model,
        string $className,
        int $id
    ): self {
        $entityManager
            ->shouldReceive('find')
            ->with($className, $id)
            ->andReturn($model);

        return $this;
    }

    /**
     * @return UnitOfWork|MockInterface
     */
    private function createUnitOfWork()
    {
        return m::spy(UnitOfWork::class);
    }

    /**
     * @param UnitOfWork|MockInterface $unitOfWork
     * @param EntityPersister          $entityPersister
     * @param string                   $className
     *
     * @return $this
     */
    private function mockUnitOfWorkGetEntityPersister(
        MockInterface $unitOfWork,
        EntityPersister $entityPersister,
        string $className
    ): self {
        $unitOfWork
            ->shouldReceive('getEntityPersister')
            ->with($className)
            ->andReturn($entityPersister);

        return $this;
    }

    /**
     * @return EntityPersister|MockInterface
     */
    private function createEntityPersister()
    {
        return m::spy(EntityPersister::class);
    }

    /**
     * @param EntityPersister|MockInterface $entityPersister
     * @param MockInterface|null            $model
     * @param array                         $criteria
     *
     * @return $this
     */
    private function mockEntityPersisterLoad(
        MockInterface $entityPersister,
        ?MockInterface $model,
        array $criteria
    ): self {
        $entityPersister
            ->shouldReceive('load')
            ->with($criteria)
            ->andReturn($model);

        return $this;
    }

    /**
     * @param EntityPersister|MockInterface $entityPersister
     * @param array                         $models
     * @param array                         $criteria
     * @param array                         $orderBy
     * @param int                           $limit
     * @param int                           $offset
     *
     * @return $this
     */
    private function mockEntityPersisterLoadAll(
        MockInterface $entityPersister,
        array $models,
        array $criteria,
        array $orderBy,
        int $limit,
        int $offset
    ): self {
        $entityPersister
            ->shouldReceive('loadAll')
            ->with($criteria, $orderBy, $limit, $offset)
            ->andReturn($models);

        return $this;
    }

    /**
     * @param EntityPersister|MockInterface $entityPersister
     * @param array                         $models
     * @param Criteria                      $criteria
     *
     * @return $this
     */
    private function mockEntityPersisterLoadCriteria(
        MockInterface $entityPersister,
        array $models,
        Criteria $criteria
    ): self {
        $entityPersister
            ->shouldReceive('loadCriteria')
            ->with($criteria)
            ->andReturn($models);

        return $this;
    }

    /**
     * @param EntityManager|MockInterface $entityManager
     * @param ModelInterface              $model
     *
     * @return $this
     */
    private function assertEntityManagerPersist(MockInterface $entityManager, ModelInterface $model): self
    {
        $entityManager
            ->shouldHaveReceived('persist')
            ->with($model)
            ->once();

        return $this;
    }

    /**
     * @param EntityManager|MockInterface $entityManager
     *
     * @return $this
     */
    private function assertEntityManagerFlush(MockInterface $entityManager): self
    {
        $entityManager
            ->shouldHaveReceived('flush')
            ->once();

        return $this;
    }

    /**
     * @param EntityManager|MockInterface $entityManager
     * @param ModelInterface              $model
     *
     * @return $this
     */
    private function assertEntityManagerRemove(MockInterface $entityManager, ModelInterface $model): self
    {
        $entityManager
            ->shouldHaveReceived('remove')
            ->with($model)
            ->once();

        return $this;
    }
}
