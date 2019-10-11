<?php

namespace App\Models\Project;

use App\Models\ModelInterface;
use App\Models\ModelParameterValidation;
use App\Models\UuidCreate;
use App\Services\Uuid\UuidFactory;

/**
 * Class ProjectInviteDoctrineModelFactory
 *
 * @package App\Models\Project
 */
final class ProjectInviteDoctrineModelFactory implements ProjectInviteModelFactory
{
    use ModelParameterValidation;
    use UuidCreate;

    /**
     * @var ProjectModelFactory
     */
    private $projectModelFactory;

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
     * @param ProjectModelFactory $projectModelFactory
     *
     * @return $this
     */
    public function setProjectModelFactory(ProjectModelFactory $projectModelFactory): ProjectInviteModelFactory
    {
        $this->projectModelFactory = $projectModelFactory;

        return $this;
    }

    /**
     * @return ProjectModelFactory
     */
    private function getProjectModelFactory(): ProjectModelFactory
    {
        return $this->projectModelFactory;
    }

    /**
     * @param array $data
     *
     * @return ProjectInviteModel|ModelInterface
     */
    public function create(array $data): ModelInterface
    {
        return (new ProjectInviteDoctrineModel(
            $this->getUuidFactory()->create(),
            $this->validateStringParameter($data, ProjectInviteModel::PROPERTY_TOKEN),
            $this->validateEmailParameter($data, ProjectInviteModel::PROPERTY_EMAIL),
            $this->validateProjectModel($data),
            $this->validateDateTimeParameter($data, ProjectInviteModel::PROPERTY_CREATED_AT, false),
            $this->validateDateTimeParameter($data, ProjectInviteModel::PROPERTY_UPDATED_AT, false)
        ))->setId($this->validateIntegerParameter($data, ProjectInviteModel::PROPERTY_ID, false));
    }

    /**
     * @param ProjectInviteModel|ModelInterface $model
     * @param array                             $data
     *
     * @return ProjectInviteModel|ModelInterface
     */
    public function fill(ModelInterface $model, array $data): ModelInterface
    {
        $token = $this->validateStringParameter($data, ProjectInviteModel::PROPERTY_TOKEN, false);
        if (!empty($token)) {
            $model->setToken($token);
        }

        $email = $this->validateEmailParameter($data, ProjectInviteModel::PROPERTY_EMAIL, false);
        if (!empty($email)) {
            $model->setEmail($email);
        }

        $project = $this->validateProjectModel($data, false);
        if (!empty($project)) {
            $model->setProject($project);
        }

        $createdAt = $this->validateDateTimeParameter($data, ProjectInviteModel::PROPERTY_CREATED_AT, false);
        if (!empty($createdAt)) {
            $model->setCreatedAt($createdAt);
        }

        $updatedAt = $this->validateDateTimeParameter($data, ProjectInviteModel::PROPERTY_UPDATED_AT, false);
        if (!empty($updatedAt)) {
            $model->setUpdatedAt($updatedAt);
        }

        $id = $this->validateIntegerParameter($data, ProjectInviteModel::PROPERTY_ID, false);
        if (!empty($id)) {
            $model->setId($id);
        }

        return $model;
    }

    /**
     * @param array $data
     * @param bool  $required
     *
     * @return ProjectModel|ModelInterface|null
     */
    private function validateProjectModel(array $data, bool $required = true): ?ProjectModel
    {
        return $this->validateModelParameter(
            $data,
            'project',
            $this->getProjectModelFactory(),
            ProjectModel::class,
            $required
        );
    }
}
