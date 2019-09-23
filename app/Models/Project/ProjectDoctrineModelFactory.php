<?php

namespace App\Models\Project;

use App\Exceptions\InvalidParameterException;
use App\Models\ModelInterface;
use App\Models\ModelParameterValidation;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Models\Uuid;
use App\Services\Uuid\UuidFactory;

/**
 * Class ProjectDoctrineModelFactory
 *
 * @package App\Models\Project
 */
final class ProjectDoctrineModelFactory implements ProjectModelFactory
{
    use ModelParameterValidation;
    use Uuid;

    /**
     * @var UserModelFactoryInterface
     */
    private $userModelFactory;

    /**
     * ProjectDoctrineModelFactory constructor.
     *
     * @param UuidFactory $uuidFactory
     */
    public function __construct(UuidFactory $uuidFactory)
    {
        $this->uuidFactory = $uuidFactory;
    }

    /**
     * @param UserModelFactoryInterface $userModelFactory
     *
     * @return ProjectModelFactory
     */
    public function setUserModelFactory(UserModelFactoryInterface $userModelFactory): ProjectModelFactory
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
     * @return ProjectModel|ModelInterface
     */
    public function create(array $data): ModelInterface
    {
        return (new ProjectDoctrineModel(
            $this->getUuidFactory()->create(),
            $this->validateStringParameter($data, ProjectModel::PROPERTY_LABEL),
            $this->validateUserModel($data),
            $this->validateStringParameter($data, ProjectModel::PROPERTY_DESCRIPTION, false),
            $this->validateDateTimeParameter($data, ProjectModel::PROPERTY_CREATED_AT, false),
            $this->validateDateTimeParameter($data, ProjectModel::PROPERTY_UPDATED_AT, false),
            $this->validateDateTimeParameter($data, ProjectModel::PROPERTY_DELETED_AT, false)
        ))->setId($this->validateIntegerParameter($data, ProjectModel::PROPERTY_ID, false));
    }

    /**
     * @param ProjectModel|ModelInterface $model
     * @param array                       $data
     *
     * @return ProjectModel|ModelInterface
     */
    public function fill(ModelInterface $model, array $data): ModelInterface
    {
        $label = $this->validateStringParameter($data, ProjectModel::PROPERTY_LABEL, false);
        if (!empty($label)) {
            $model->setLabel($label);
        }

        $user = $this->validateUserModel($data, false);
        if ($user) {
            $model->setUser($user);
        }

        $description = $this->validateStringParameter(
            $data,
            ProjectModel::PROPERTY_DESCRIPTION,
            false,
            true
        );
        if ($description !== null) {
            $model->setDescription($description);
        }

        $createdAt = $this->validateDateTimeParameter($data, ProjectModel::PROPERTY_CREATED_AT, false);
        if (!empty($createdAt)) {
            $model->setCreatedAt($createdAt);
        }

        $updatedAt = $this->validateDateTimeParameter($data, ProjectModel::PROPERTY_UPDATED_AT, false);
        if (!empty($updatedAt)) {
            $model->setUpdatedAt($updatedAt);
        }

        $deletedAt = $this->validateDateTimeParameter($data, ProjectModel::PROPERTY_DELETED_AT, false);
        if (!empty($deletedAt)) {
            $model->setDeletedAt($deletedAt);
        }

        $id = $this->validateIntegerParameter($data, ProjectModel::PROPERTY_ID, false);
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
            ProjectModel::PROPERTY_USER,
            $this->getUserModelFactory(),
            UserModelInterface::class,
            $required
        );
    }
}
