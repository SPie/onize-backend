<?php

namespace App\Repositories;

/**
 * Interface QueryBuilder
 *
 * @package App\Repositories
 */
interface QueryBuilder
{
    /**
     * @param string      $table
     * @param string|null $alias
     *
     * @return $this
     */
    public function update(string $table, string $alias = null): self;

    /**
     * @param string $column
     * @param string $value
     *
     * @return $this
     */
    public function set(string $column, string $value): self;

    /**
     * @param string $expression
     *
     * @return $this
     */
    public function where(string $expression): self;

    /**
     * @param string $expression
     *
     * @return $this
     */
    public function andWhere(string $expression): self;

    /**
     * @param string $parameter
     * @param        $value
     *
     * @return $this
     */
    public function setParameter(string $parameter, $value): self;

    /**
     * @return Query
     */
    public function getQuery(): Query;
}
