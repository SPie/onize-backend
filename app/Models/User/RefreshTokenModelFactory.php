<?php

namespace App\Models\User;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;

/**
 * Interface RefreshTokenModelFactory
 *
 * @package App\Models\User
 */
interface RefreshTokenModelFactory extends ModelFactoryInterface
{

    /**
     * @param UserModelFactoryInterface $userModelFactory
     *
     * @return RefreshTokenModelFactory
     */
    public function setUserModelFactory(UserModelFactoryInterface $userModelFactory): RefreshTokenModelFactory;

    /**
     * @param array $data
     *
     * @return RefreshTokenModel|ModelInterface
     */
    public function create(array $data): ModelInterface;

    /**
     * @param RefreshTokenModel|ModelInterface $model
     * @param array                            $data
     *
     * @return RefreshTokenModel|ModelInterface
     */
    public function fill(ModelInterface $model, array $data): ModelInterface;
}
