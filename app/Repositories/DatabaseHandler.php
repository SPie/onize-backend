<?php

namespace App\Repositories;

use App\Models\ModelInterface;
use Doctrine\Common\Collections\Criteria;
use Illuminate\Support\Collection;

/**
 * Interface DatabaseHandler
 *
 * @package App\Repositories
 */
interface DatabaseHandler
{
    /**
     * @param int $id
     *
     * @return ModelInterface|null
     */
    public function find(int $id): ?ModelInterface;

    /**
     * @param array $criteria
     *
     * @return ModelInterface|null
     */
    public function load(array $criteria): ?ModelInterface;

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
    ): Collection;

    /**
     * @param Criteria $criteria
     *
     * @return Collection
     */
    public function loadByCriteria(Criteria $criteria): Collection;

    /**
     * @param ModelInterface $model
     * @param bool           $flush
     *
     * @return ModelInterface
     */
    public function save(ModelInterface $model, bool $flush): ModelInterface;

    /**
     * @param ModelInterface $model
     * @param bool           $flush
     *
     * @return $this
     */
    public function delete(ModelInterface $model, bool $flush): self;

    /**
     * @return $this
     */
    public function flush(): self;

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder(): QueryBuilder;
}
