<?php

namespace App\Repositories;

use Doctrine\ORM\QueryBuilder as RealQueryBuilder;

/**
 * Class DoctrineQueryBuilder
 *
 * @package App\Repositories
 */
final class DoctrineQueryBuilder implements QueryBuilder
{
    /**
     * @var RealQueryBuilder
     */
    private $realQueryBuilder;

    /**
     * DoctrineQueryBuilder constructor.
     *
     * @param RealQueryBuilder $realQueryBuilder
     */
    public function __construct(RealQueryBuilder $realQueryBuilder)
    {
        $this->realQueryBuilder = $realQueryBuilder;
    }

    /**
     * @return RealQueryBuilder
     */
    private function getRealQueryBuilder(): RealQueryBuilder
    {
        return $this->realQueryBuilder;
    }

    /**
     * @inheritDoc
     */
    public function update(string $table): QueryBuilder
    {
        $this->realQueryBuilder = $this->getRealQueryBuilder()->update($table);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function set(string $column, string $value): QueryBuilder
    {
        $this->realQueryBuilder = $this->getRealQueryBuilder()->set($column, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function where(string $expression): QueryBuilder
    {
        $this->realQueryBuilder = $this->getRealQueryBuilder()->where($expression);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function andWhere(string $expression): QueryBuilder
    {
        $this->realQueryBuilder = $this->getRealQueryBuilder()->andWhere($expression);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setParameter(string $parameter, $value): QueryBuilder
    {
        $this->realQueryBuilder = $this->getRealQueryBuilder()->setParameter($parameter, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getQuery(): Query
    {
        return new DoctrineQuery($this->getRealQueryBuilder()->getQuery());
    }
}
