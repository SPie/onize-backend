<?php

namespace App\Models\User;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;

/**
 * Interface PasswordResetTokenModelFactory
 *
 * @package App\Models\User
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