<?php

namespace App\Repositories;

use App\Models\ModelInterface;
use Doctrine\Common\Collections\Criteria;
use Illuminate\Support\Collection;

/**
 * Class AbstractDoctrineRepository
 *
 * @package App\Repositories
 */
abstract class AbstractDoctrineRepository implements RepositoryInterface
{
    /**
     * @var DatabaseHandler
     */
    private $databaseHandler;

    /**
     * AbstractDoctrineRepository constructor.
     *
     * @param DatabaseHandler $databaseHandler
     */
    public function __construct(DatabaseHandler $databaseHandler)
    {
        $this->databaseHandler = $databaseHandler;
    }

    /**
     * @return DatabaseHandler
     */
    private function getDatabaseHandler()
    {
        return $this->databaseHandler;
    }

    /**
     * @param int $id
     *
     * @return ModelInterface|null
     */
    public function find($id): ?ModelInterface
    {
        return $this->getDatabaseHandler()->find($id);
    }

    /**
     * @return Collection
     */
    public function findAll(): Collection
    {
        return $this->getDatabaseHandler()->loadAll();

    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return Collection
     */
    public function findBy(array $criteria = [], array $orderBy = null, $limit = null, $offset = null): Collection
    {
        return $this->getDatabaseHandler()->loadAll($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param array $criteria
     *
     * @return ModelInterface|null
     */
    public function findOneBy(array $criteria): ?ModelInterface
    {
        return $this->getDatabaseHandler()->load($criteria);
    }

    /**
     * @param Criteria $criteria
     *
     * @return Collection
     */
    public function findByCriteria(Criteria $criteria): Collection
    {
        return $this->getDatabaseHandler()->loadByCriteria($criteria);
    }

    /**
     * @param ModelInterface $model
     * @param bool           $flush
     *
     * @return ModelInterface
     */
    public function save(ModelInterface $model, bool $flush = true): ModelInterface
    {
        return $this->getDatabaseHandler()->save($model, $flush);
    }

    /**
     * @param ModelInterface $model
     * @param bool           $flush
     *
     * @return $this
     */
    public function delete(ModelInterface $model, bool $flush = true)
    {
        $this->getDatabaseHandler()->delete($model, $flush);

        return $this;
    }

    /**
     * @return $this
     */
    public function flush()
    {
        $this->getDatabaseHandler()->flush();

        return $this;
    }
}
