<?php

namespace App\Repositories;

use App\Models\ModelInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\LazyCriteriaCollection;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Illuminate\Support\Collection;

/**
 * Class DoctrineDatabaseHandler
 *
 * @package App\Repositories
 */
final class DoctrineDatabaseHandler implements DatabaseHandler
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $className;

    /**
     * DoctrineDatabaseHandler constructor.
     *
     * @param EntityManager $entityManager
     * @param string        $className
     */
    public function __construct(EntityManager $entityManager, string $className)
    {
        $this->entityManager = $entityManager;
        $this->className = $className;
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @return string
     */
    private function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param int $id
     *
     * @return ModelInterface|object|null
     */
    public function find(int $id): ?ModelInterface
    {
        return $this->getEntityManager()->find($this->getClassName(), $id);
    }

    /**
     * @param array $criteria
     *
     * @return ModelInterface|object|null
     */
    public function load(array $criteria): ?ModelInterface
    {
        return $this->getEntityPersister()->load($criteria);
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return Collection
     */
    public function loadAll(
        array $criteria = [],
        array $orderBy = null,
        int $limit = null,
        int $offset = null
    ): Collection {
        return new Collection($this->getEntityPersister()->loadAll(
            $criteria,
            $orderBy,
            $limit,
            $offset
        ));
    }

    /**
     * @param Criteria $criteria
     *
     * @return Collection
     */
    public function loadByCriteria(Criteria $criteria): Collection
    {
        return new Collection((new LazyCriteriaCollection($this->getEntityPersister(), $criteria))->getValues());
    }

    /**
     * @param ModelInterface $model
     * @param bool           $flush
     *
     * @return ModelInterface
     */
    public function save(ModelInterface $model, bool $flush): ModelInterface
    {
        $this->getEntityManager()->persist($model);

        if ($flush) {
            $this->flush();
        }

        return $model;
    }

    /**
     * @param ModelInterface $model
     * @param bool           $flush
     *
     * @return $this
     */
    public function delete(ModelInterface $model, bool $flush): DatabaseHandler
    {
        $this->getEntityManager()->remove($model);

        if ($flush) {
            $this->flush();
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function flush(): DatabaseHandler
    {
        $this->getEntityManager()->flush();

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder(): QueryBuilder
    {
        return new DoctrineQueryBuilder($this->getEntityManager()->createQueryBuilder());
    }

    /**
     * @return EntityPersister
     */
    private function getEntityPersister(): EntityPersister
    {
        return $this->getEntityManager()->getUnitOfWork()->getEntityPersister($this->getClassName());
    }
}
