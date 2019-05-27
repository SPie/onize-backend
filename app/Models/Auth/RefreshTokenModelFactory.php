<?php

namespace App\Models\Auth;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;
use App\Models\User\UserModelFactoryInterface;

/**
 * Interface RefreshTokenModelFactory
 *
 * @package App\Models\Auth
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
