<?php

namespace Test;

use App\Models\ModelInterface;
use App\Repositories\RepositoryInterface;
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
}
