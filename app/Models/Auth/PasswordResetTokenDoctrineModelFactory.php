<?php

namespace App\Models\Auth;

use App\Exceptions\InvalidParameterException;
use App\Models\ModelInterface;
use App\Models\ModelParameterValidation;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;

/**
 * Class PasswordResetTokenDoctrineModelFactory
 *
 * @package App\Models\Auth
 */
final class PasswordResetTokenDoctrineModelFactory implements PasswordResetTokenModelFactory
{

    use ModelParameterValidation;

    /**
     * @var UserModelFactoryInterface
     */
    private $userModelFactory;

    /**
     * @param UserModelFactoryInterface $userModelFactory
     *
     * @return PasswordResetTokenModelFactory
     */
    public function setUserModelFactory(UserModelFactoryInterface $userModelFactory): PasswordResetTokenModelFactory
    {
        $this->userModelFactory = $userModelFactory;

        return $this;
    }

    /**
     * @return UserModelFactoryInterface
     */
    private function getUserModelFactory(): UserModelFactoryInterface
    {
        return $this->userModelFactory;
    }

    /**
     * @param array $data
     *
     * @return PasswordResetTokenModel|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function create(array $data): ModelInterface
    {
        return (new PasswordResetTokenDoctrineModel(
            $this->validateStringParameter($data, PasswordResetTokenModel::PROPERTY_TOKEN),
            $this->validateDateTimeParameter($data, PasswordResetTokenModel::PROPERTY_VALID_UNTIL),
            $this->validateUserModel($data),
            $this->validateDateTimeParameter($data, PasswordResetTokenModel::PROPERTY_CREATED_AT, false),
            $this->validateDateTimeParameter($data, PasswordResetTokenModel::PROPERTY_UPDATED_AT, false)
        ))->setId($this->validateIntegerParameter($data, PasswordResetTokenModel::PROPERTY_ID, false));
    }

    /**
     * @param PasswordResetTokenModel|ModelInterface $model
     * @param array                                  $data
     *
     * @return PasswordResetTokenModel|ModelInterface
     */
    public function fill(ModelInterface $model, array $data): ModelInterface
    {
        $token = $this->validateStringParameter($data, PasswordResetTokenDoctrineModel::PROPERTY_TOKEN, false);
        if (!empty($token)) {
            $model->setToken($token);
        }
        $validUntil = $this->validateDateTimeParameter($data, PasswordResetTokenModel::PROPERTY_VALID_UNTIL, false);
        if (!empty($validUntil)) {
            $model->setValidUntil($validUntil);
        }
        $user = $this->validateUserModel($data, false);
        if (!empty($user)) {
            $model->setUser($user);
        }
        $createdAt = $this->validateDateTimeParameter($data, PasswordResetTokenModel::PROPERTY_CREATED_AT, false);
        if (!empty($createdAt)) {
            $model->setCreatedAt($createdAt);
        }
        $updatedAt = $this->validateDateTimeParameter($data, PasswordResetTokenModel::PROPERTY_UPDATED_AT, false);
        if (!empty($updatedAt)) {
            $model->setUpdatedAt($updatedAt);
        }
        $id = $this->validateIntegerParameter($data, PasswordResetTokenModel::PROPERTY_ID, false);
        if (!empty($id)) {
            $model->setId($id);
        }

        return $model;
    }

    /**
     * @param array $data
     * @param bool  $required
     *
     * @return UserModelInterface|ModelInterface|null
     *
     * @throws InvalidParameterException
     */
    private function validateUserModel(array $data, bool $required = true): ?UserModelInterface
    {
        return $this->validateModelParameter(
            $data,
            PasswordResetTokenModel::PROPERTY_USER,
            $this->getUserModelFactory(),
            UserModelInterface::class,
            $required
        );
    }
}