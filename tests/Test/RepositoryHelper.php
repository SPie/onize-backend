<?php

namespace Test;

use App\Models\ModelInterface;
use App\Repositories\RepositoryInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\UnitOfWork;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;

/**
 * Trait RepositoryHelper
 *
 * @package Test
 */
trait RepositoryHelper
{
    /**
     * @return EntityManagerInterface|MockInterface
     */
    private function createEntityManager(): EntityManagerInterface
    {
        return Mockery::spy(EntityManagerInterface::class);
    }

    /**
     * @param EntityManagerInterface|MockInterface $entityManager
     * @param UnitOfWork                           $unitOfWork
     *
     * @return $this
     */
    private function mockEntityManagerGetUnitOfWork(MockInterface $entityManager, UnitOfWork $unitOfWork)
    {
        $entityManager
            ->shouldReceive('getUnitOfWork')
            ->andReturn($unitOfWork);

        return $this;
    }

    /**
     * @param string|null $className
     *
     * @return ClassMetadata|MockInterface
     */
    private function createClassMetaData(string $className = null): ClassMetadata
    {
        return Mockery::spy(ClassMetadata::class)
            ->shouldReceive('getName')
            ->andReturn($className ?: $this->getFaker()->uuid)
            ->getMock();
    }

    private function createUnitOfWork(): UnitOfWork
    {
        return Mockery::spy(UnitOfWork::class);
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
    ) {
        $unitOfWork
            ->shouldReceive('getEntityPersister')
            ->with($className)
            ->andReturn($entityPersister);

        return $this;
    }

    /**
     * @return EntityPersister|MockInterface
     */
    private function createEntityPersister(): EntityPersister
    {
        return Mockery::spy(EntityPersister::class);
    }

    /**
     * @param EntityPersister|MockInterface $entityPersister
     * @param array                         $entities
     * @param Criteria                      $criteria
     *
     * @return $this
     */
    private function mockEntityPersisterLoadCriteria(MockInterface $entityPersister, array $entities, Criteria $criteria)
    {
        $entityPersister
            ->shouldReceive('loadCriteria')
            ->with(Mockery::on(function ($argument) use ($criteria) {
                return $argument == $criteria;
            }))
            ->andReturn($entities);

        return $this;
    }

    /**
     * @param array $values
     *
     * @return Collection
     */
    private function createCollection(array $values = []): Collection
    {
        return new Collection($values);
    }

    /**
     * @param RepositoryInterface|MockInterface $repository
     * @param ModelInterface|null               $model
     * @param int                               $id
     *
     * @return $this
     */
    protected function mockRepositoryFind(MockInterface $repository, ?ModelInterface $model, int $id)
    {
        $repository
            ->shouldReceive('find')
            ->with($id)
            ->andReturn($model);

        return $this;
    }

    /**
     * @param MockInterface  $repository
     * @param ModelInterface $model
     *
     * @return $this
     */
    protected function mockRepositorySave(MockInterface $repository, ModelInterface $model)
    {
        $repository
            ->shouldReceive('save')
            ->with(Mockery::on(function ($argument) use ($model) {
                return $argument == $model;
            }))
            ->andReturn($model);

        return $this;
    }

    //region Assertions

    /**
     * @param RepositoryInterface|MockInterface $repository
     * @param ModelInterface                    $model
     * @param int                               $times
     *
     * @return $this
     */
    private function assertRepositorySave(MockInterface $repository, ModelInterface $model, int $times = 1)
    {
        $repository
            ->shouldHaveReceived('save')
            ->with(Mockery::on(function ($argument) use ($model) {
                return $argument == $model;
            }))
            ->times($times);

        return $this;
    }

    /**
     * @param MockInterface  $repository
     * @param ModelInterface $model
     *
     * @return $this
     */
    private function assertRepositoryDelete(MockInterface $repository, ModelInterface $model): self
    {
        $repository
            ->shouldHaveReceived('delete')
            ->with($model)
            ->once();

        return $this;
    }

    //endregion
}
