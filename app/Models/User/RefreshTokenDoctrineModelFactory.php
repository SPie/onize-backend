<?php

namespace App\Models\User;

use App\Exceptions\InvalidParameterException;
use App\Models\ModelInterface;
use App\Models\ModelParameterValidation;

/**
 * Class RefreshTokenDoctrineModelFactory
 *
 * @package App\Models\User
 */
final class RefreshTokenDoctrineModelFactory implements RefreshTokenModelFactory
{
    use ModelParameterValidation;

    /**
     * @var UserModelFactoryInterface
     */
    private $userModelFactory;

    /**
     * @param UserModelFactoryInterface $userModelFactory
     *
     * @return RefreshTokenModelFactory
     */
    public function setUserModelFactory(UserModelFactoryInterface $userModelFactory): RefreshTokenModelFactory
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
     * @return RefreshTokenModel|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function create(array $data): ModelInterface
    {
        return (new RefreshTokenDoctrineModel(
            $this->validateStringParameter($data, RefreshTokenModel::PROPERTY_IDENTIFIER),
            $this->validateUserModel($data),
            $this->validateDateTimeParameter($data, RefreshTokenModel::PROPERTY_VALID_UNTIL, false),
            $this->validateDateTimeParameter($data, RefreshTokenModel::PROPERTY_CREATED_AT, false),
            $this->validateDateTimeParameter($data, RefreshTokenModel::PROPERTY_UPDATED_AT, false)
        ))->setId($this->validateIntegerParameter($data, RefreshTokenModel::PROPERTY_ID, false));
    }

    /**
     * @param RefreshTokenModel|ModelInterface $model
     * @param array                            $data
     *
     * @return RefreshTokenModel|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function fill(ModelInterface $model, array $data): ModelInterface
    {
        $identifier = $this->validateStringParameter(
            $data,
            RefreshTokenModel::PROPERTY_IDENTIFIER,
            false
        );
        if (!empty($identifier)) {
            $model->setIdentifier($identifier);
        }

        $user = $this->validateUserModel($data, false);
        if (!empty($user)) {
            $model->setUser($user);
        }

        $validUntil = $this->validateDateTimeParameter(
            $data,
            RefreshTokenModel::PROPERTY_VALID_UNTIL,
            false
        );
        if (!empty($validUntil)) {
            $model->setValidUntil($validUntil);
        }

        $createdAt = $this->validateDateTimeParameter(
            $data,
            RefreshTokenModel::PROPERTY_CREATED_AT,
            false
        );
        if (!empty($createdAt)) {
            $model->setCreatedAt($createdAt);
        }

        $updatedAt = $this->validateDateTimeParameter(
            $data,
            RefreshTokenModel::PROPERTY_UPDATED_AT,
            false
        );
        if (!empty($updatedAt)) {
            $model->setUpdatedAt($updatedAt);
        }

        $id = $this->validateIntegerParameter($data, RefreshTokenModel::PROPERTY_ID, false);
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
            RefreshTokenModel::PROPERTY_USER,
            $this->getUserModelFactory(),
            UserModelInterface::class,
            $required
        );
    }
}
