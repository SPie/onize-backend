<?php

namespace App\Models\User;

use App\Exceptions\InvalidParameterException;
use App\Models\ModelInterface;

/**
 * Class LoginAttemptDoctrineModelFactory
 *
 * @package App\Models\User
 */
final class LoginAttemptDoctrineModelFactory implements LoginAttemptModelFactory
{

    /**
     * @param array $data
     *
     * @return ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function create(array $data): ModelInterface
    {
        // TODO: Implement create() method.
    }

    /**
     * @param ModelInterface $model
     * @param array          $data
     *
     * @return ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function fill(ModelInterface $model, array $data): ModelInterface
    {
        // TODO: Implement fill() method.
    }
}