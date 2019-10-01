<?php

namespace App\Models\User;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;

/**
 * Interface LoginAttemptModelFactory
 *
 * @package App\Models\User
 */
interface LoginAttemptModelFactory extends ModelFactoryInterface
{
    /**
     * @param array $data
     *
     * @return LoginAttemptModel|ModelInterface
     */
    public function create(array $data): ModelInterface;

    /**
     * @param LoginAttemptModel|ModelInterface $model
     * @param array                            $data
     *
     * @return LoginAttemptModel|ModelInterface
     */
    public function fill(ModelInterface $model, array $data): ModelInterface;
}
