<?php

namespace Test;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;
use App\Repositories\DatabaseHandler;
use App\Services\Uuid\UuidFactory;
use Doctrine\Common\Collections\Criteria;
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
     * @return ModelInterface
     */
    private function createModel(): ModelInterface
    {
        return Mockery::spy(ModelInterface::class);
    }

    /**
     * @param ModelInterface|MockInterface $model
     * @param array                        $array
     * @param int|null                     $depth
     *
     * @return $this
     */
    private function mockModelToArray(MockInterface $model, array $array, int $depth = null): self
    {
        $arguments = [];
        if ($depth !== null) {
            $arguments[] = $depth;
        }

        $model
            ->shouldReceive('toArray')
            ->withArgs($arguments)
            ->andReturn($array);

        return $this;
    }

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

    /**
     * @return UuidFactory
     */
    private function createUuidFactory(): UuidFactory
    {
        return Mockery::spy(UuidFactory::class);
    }

    /**
     * @param UuidFactory|MockInterface $uuidFactory
     * @param string                    $uuid
     *
     * @return $this
     */
    private function mockUuidFactoryCreate(MockInterface $uuidFactory, string $uuid)
    {
        $uuidFactory
            ->shouldReceive('create')
            ->andReturn($uuid);

        return $this;
    }

    /**
     * @param string $uuid
     *
     * @return UuidFactory
     */
    private function createUuidFactoryWithUuid(string $uuid = null): UuidFactory
    {
        $uuidFactory = $this->createUuidFactory();
        $this->mockUuidFactoryCreate($uuidFactory, $uuid ?: $this->getFaker()->uuid);

        return $uuidFactory;
    }

    /**
     * @return DatabaseHandler|MockInterface
     */
    private function createDatabaseHandler(): DatabaseHandler
    {
        return Mockery::spy(DatabaseHandler::class);
    }

    /**
     * @param DatabaseHandler|MockInterface $databaseHandler
     * @param ModelInterface|null           $model
     * @param array                         $criteria
     *
     * @return $this
     */
    private function mockDatabaseHandlerLoad(
        MockInterface $databaseHandler,
        ?ModelInterface $model,
        array $criteria
    ): self {
        $databaseHandler
            ->shouldReceive('load')
            ->with($criteria)
            ->andReturn($model);

        return $this;
    }

    /**
     * @param DatabaseHandler|MockInterface $databaseHandler
     * @param Collection                    $models
     * @param Criteria                      $criteria
     *
     * @return $this
     */
    private function mockDatabaseHandlerLoadByCriteria(
        MockInterface $databaseHandler,
        Collection $models,
        Criteria $criteria
    ) {
        $databaseHandler
            ->shouldReceive('loadByCriteria')
            ->with(Mockery::on(function ($argument) use ($criteria) {
                return $argument == $criteria;
            }))
            ->andReturn($models);

        return $this;
    }
}
