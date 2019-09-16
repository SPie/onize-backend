<?php

namespace App\Models\User;

use App\Exceptions\InvalidParameterException;
use App\Models\ModelInterface;
use App\Models\ModelParameterValidation;

/**
 * Class UserDoctrineModelFactory
 *
 * @package App\Models\User
 */
class UserDoctrineModelFactory implements UserModelFactoryInterface
{
    use ModelParameterValidation;

    /**
     * @var RefreshTokenModelFactory
     */
    private $refreshTokenModelFactory;

    /**
     * @param RefreshTokenModelFactory $refreshTokenModelFactory
     *
     * @return UserModelFactoryInterface|UserDoctrineModelFactory
     */
    public function setRefreshTokenModelFactory(
        RefreshTokenModelFactory $refreshTokenModelFactory
    ): UserModelFactoryInterface {
        $this->refreshTokenModelFactory = $refreshTokenModelFactory;

        return $this;
    }

    /**
     * @return RefreshTokenModelFactory
     */
    protected function getRefreshTokenModelFactory(): RefreshTokenModelFactory
    {
        return $this->refreshTokenModelFactory;
    }

    /**
     * @param array $data
     *
     * @return UserModelInterface|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function create(array $data): ModelInterface
    {
        return (new UserDoctrineModel(
            $this->validateStringParameter($data, UserModelInterface::PROPERTY_EMAIL),
            $this->validateStringParameter($data, UserModelInterface::PROPERTY_PASSWORD),
            $this->validateRefreshTokens($data),
            [], // TODO
            $this->validateDateTimeParameter($data, UserModelInterface::PROPERTY_CREATED_AT, false),
            $this->validateDateTimeParameter($data, UserModelInterface::PROPERTY_UPDATED_AT, false),
            $this->validateDateTimeParameter($data, UserModelInterface::PROPERTY_DELETED_AT, false)
        ))->setId($this->validateIntegerParameter($data, UserModelInterface::PROPERTY_ID, false));
    }

    /**
     * @param UserModelInterface|ModelInterface $model
     * @param array                             $data
     *
     * @return UserModelInterface|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function fill(ModelInterface $model, array $data): ModelInterface
    {
        $email = $this->validateStringParameter($data, UserModelInterface::PROPERTY_EMAIL, false);
        if (!empty($email)) {
            $model->setEmail($email);
        }
        $password = $this->validateStringParameter(
            $data,
            UserModelInterface::PROPERTY_PASSWORD,
            false
        );
        if (!empty($password)) {
            $model->setPassword($password);
        }
        $id = $this->validateIntegerParameter($data, UserModelInterface::PROPERTY_ID, false);
        if (!empty($id)) {
            $model->setId($id);
        }
        $createdAt = $this->validateDateTimeParameter(
            $data,
            UserModelInterface::PROPERTY_CREATED_AT,
            false
        );
        if (!empty($createdAt)) {
            $model->setCreatedAt($createdAt);
        }
        $updatedAt = $this->validateDateTimeParameter(
            $data,
            UserModelInterface::PROPERTY_UPDATED_AT,
            false
        );
        if (!empty($updatedAt)) {
            $model->setUpdatedAt($updatedAt);
        }
        $deletedAt = $this->validateDateTimeParameter(
            $data,
            UserModelInterface::PROPERTY_DELETED_AT,
            false
        );
        if (!empty($deletedAt)) {
            $model->setDeletedAt($deletedAt);
        }
        $refreshTokens = $this->validateRefreshTokens($data);
        if (!empty($refreshTokens)) {
            $model->setRefreshTokens($refreshTokens);
        }

        return $model;
    }

    /**
     * @param array $data
     *
     * @return array
     *
     * @throws InvalidParameterException
     */
    protected function validateRefreshTokens(array $data): array
    {
        $refreshTokens = $this->validateArrayParameter(
            $data,
            UserModelInterface::PROPERTY_REFRESH_TOKENS,
            false
        );

        return \is_array($refreshTokens)
            ? \array_map(
                function ($refreshToken) {
                    if ($refreshToken instanceof RefreshTokenModel) {
                        return $refreshToken;
                    }

                    if (\is_array($refreshToken)) {
                        return $this->getRefreshTokenModelFactory()->create($refreshToken);
                    }

                    throw new InvalidParameterException();
                },
                $refreshTokens
            )
            : [];
    }
}
