<?php

namespace Test;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;
use Doctrine\ORM\EntityManager;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;

/**
 * Trait ModelHelper
 *
 * @package Test
 */
trait ModelHelper
{
    /**
     * @param string      $modelClass
     * @param int         $times
     * @param array       $data
     * @param string|null $state
     *
     * @return Collection
     */
    protected function createModels(
        string $modelClass,
        int $times = 1,
        array $data = [],
        string $state = null
    ): Collection {
        if ($times == 1) {
            return new Collection([
                $state
                    ? entity($modelClass, $state, $times)->create($data)
                    : entity($modelClass, $times)->create($data)
            ]);
        }

        return $state
            ? entity($modelClass, $state, $times)->create($data)
            : entity($modelClass, $times)->create($data);
    }

    /**
     * @param ModelFactoryInterface|MockInterface $modelFactory
     * @param ModelInterface|\Exception           $model
     * @param array                               $data
     *
     * @return $this
     */
    protected function mockModelFactoryCreate(MockInterface $modelFactory, $model, array $data)
    {
        $modelFactory
            ->shouldReceive('create')
            ->with($data)
            ->andThrow($model);

        return $this;
    }

    /**
     * @param ModelFactoryInterface|MockInterface $modelFactory
     * @param ModelInterface|\Exception           $returnModel
     * @param array                               $data
     * @param MockInterface                       $model
     *
     * @return $this
     */
    protected function mockModelFactoryFill(MockInterface $modelFactory, $returnModel, array $data, MockInterface $model)
    {
        $modelFactory
            ->shouldReceive('fill')
            ->with(
                Mockery::on(function ($argument) use ($model) {
                    return $argument == $model;
                }),
                $data
            )
            ->andThrow($returnModel);

        return $this;
    }

    /**
     * @param string $modelName
     *
     * @return $this
     */
    private function clearModelCache(string $modelName = null)
    {
        $this->app->get(EntityManager::class)->clear($modelName);

        return $this;
    }
}
