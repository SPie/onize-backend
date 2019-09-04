<?php

namespace App\Models\User;

use App\Exceptions\InvalidParameterException;
use App\Models\ModelInterface;
use App\Models\ModelParameterValidation;

/**
 * Class LoginAttemptDoctrineModelFactory
 *
 * @package App\Models\User
 */
final class LoginAttemptDoctrineModelFactory implements LoginAttemptModelFactory
{
    use ModelParameterValidation;

    /**
     * @param array $data
     *
     * @return LoginAttemptModel|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function create(array $data): ModelInterface
    {
        return (new LoginAttemptDoctrineModel(
            $this->validateStringParameter($data, LoginAttemptModel::PROPERTY_IP_ADDRESS),
            $this->validateStringParameter($data, LoginAttemptModel::PROPERTY_IDENTIFIER),
            $this->validateDateTimeImmutableParameter($data, LoginAttemptModel::PROPERTY_ATTEMPTED_AT),
            $this->validateBooleanParameter($data, LoginAttemptModel::PROPERTY_SUCCESS)
        ))->setId($this->validateIntegerParameter($data, LoginAttemptModel::PROPERTY_ID, false));
    }

    /**
     * @param LoginAttemptModel|ModelInterface $model
     * @param array                            $data
     *
     * @return LoginAttemptModel|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function fill(ModelInterface $model, array $data): ModelInterface
    {
        $id = $this->validateIntegerParameter($data, LoginAttemptModel::PROPERTY_ID, false);
        if (!empty($id)) {
            $model->setId($id);
        }

        $ipAddress = $this->validateStringParameter(
            $data,
            LoginAttemptModel::PROPERTY_IP_ADDRESS,
            false
        );
        if (!empty($ipAddress)) {
            $model->setIpAddress($ipAddress);
        }

        $identifier = $this->validateStringParameter(
            $data,
            LoginAttemptModel::PROPERTY_IDENTIFIER,
            false
        );
        if (!empty($identifier)) {
            $model->setIdentifier($identifier);
        }

        $attemptedAt = $this->validateDateTimeImmutableParameter(
            $data,
            LoginAttemptModel::PROPERTY_ATTEMPTED_AT,
            false
        );
        if (!empty($attemptedAt)) {
            $model->setAttemptedAt($attemptedAt);
        }

        $success = $this->validateBooleanParameter(
            $data,
            LoginAttemptModel::PROPERTY_SUCCESS,
            false
        );
        if ($success !== null) {
            $model->setSuccess($success);
        }

        return $model;
    }
}
