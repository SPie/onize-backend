<?php

namespace Test;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;
use Mockery\MockInterface;

/**
 * Trait ModelHelper
 *
 * @package Test
 */
trait ModelHelper
{

    /**
     * @param ModelFactoryInterface|MockInterface $modelFactory
     * @param ModelInterface|\Exception           $model
     * @param array                               $data
     *
     * @return $this
     */
    public function mockModelFactoryCreate(MockInterface $modelFactory, $model, array $data)
    {
        $modelFactory
            ->shouldReceive('create')
            ->with($data)
            ->andThrow($model);

        return $this;
    }
}