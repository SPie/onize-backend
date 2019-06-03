<?php

namespace App\Models\Auth;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;
use App\Models\User\UserModelFactoryInterface;

/**
 * Interface PasswordResetTokenModelFactory
 *
 * @package App\Models\Auth
 */
interface PasswordResetTokenModelFactory extends ModelFactoryInterface
{

    /**
     * @param UserModelFactoryInterface $userModelFactory
     *
     * @return PasswordResetTokenModelFactory
     */
    public function setUserModelFactory(UserModelFactoryInterface $userModelFactory): PasswordResetTokenModelFactory;

    /**
     * @param array $data
     *
     * @return PasswordResetTokenModel|ModelInterface
     */
    public function create(array $data): ModelInterface;

    /**
     * @param ModelInterface $model
     * @param array          $data
     *
     * @return PasswordResetTokenModel|ModelInterface
     */
    public function fill(ModelInterface $model, array $data): ModelInterface;
}