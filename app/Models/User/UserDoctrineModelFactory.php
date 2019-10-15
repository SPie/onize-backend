<?php

namespace App\Models\User;

use App\Exceptions\InvalidParameterException;
use App\Models\ModelInterface;
use App\Models\ModelParameterValidation;
use App\Models\Project\ProjectModel;
use App\Models\Project\ProjectModelFactory;
use App\Models\UuidCreate;
use App\Services\Uuid\UuidFactory;

/**
 * Class UserDoctrineModelFactory
 *
 * @package App\Models\User
 */
class UserDoctrineModelFactory implements UserModelFactoryInterface
{
    use ModelParameterValidation;
    use UuidCreate;

    /**
     * @var RefreshTokenModelFactory
     */
    private $refreshTokenModelFactory;

    /**
     * @var ProjectModelFactory
     */
    private $projectModelFactory;

    /**
     * UserDoctrineModelFactory constructor.
     *
     * @param UuidFactory $uuidFactory
     */
    public function __construct(UuidFactory $uuidFactory)
    {
        $this->uuidFactory = $uuidFactory;
    }

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
     * @param ProjectModelFactory $projectModelFactory
     *
     * @return UserModelFactoryInterface
     */
    public function setProjectModelFactory(ProjectModelFactory $projectModelFactory): UserModelFactoryInterface
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
     * @return UserModelInterface|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function create(array $data): ModelInterface
    {
        return (new UserDoctrineModel(
            $this->getUuidFactory()->create(),
            $this->validateStringParameter($data, UserModelInterface::PROPERTY_EMAIL),
            $this->validateStringParameter($data, UserModelInterface::PROPERTY_PASSWORD),
            $this->validateDateTimeParameter($data, UserModelInterface::PROPERTY_CREATED_AT, false),
            $this->validateDateTimeParameter($data, UserModelInterface::PROPERTY_UPDATED_AT, false),
            $this->validateDateTimeParameter($data, UserModelInterface::PROPERTY_DELETED_AT, false),
            $this->validateRefreshTokens($data),
            $this->validateProjects($data),
            $this->validateJoinedProjects($data)
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

        $projects = $this->validateProjects($data);
        if (!empty($projects)) {
            $model->setProjects($projects);
        }

        if (isset($data[UserModelInterface::PROPERTY_JOINED_PROJECTS])) {
            $model->setJoinedProjects($this->validateJoinedProjects($data));
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

    /**
     * @param array $data
     *
     * @return ProjectModel[]
     *
     * @throws InvalidParameterException
     */
    private function validateProjects(array $data): array
    {
        $projects = $this->validateArrayParameter($data, UserModelInterface::PROPERTY_PROJECTS, false);

        return \is_array($projects)
            ? \array_map(
                function ($project) {
                    if ($project instanceof ProjectModel) {
                        return $project;
                    }

                    if (\is_array($project)) {
                        return $this->getProjectModelFactory()->create($project);
                    }

                    throw new InvalidParameterException();
                },
                $projects
            )
            : [];
    }

    /**
     * @param array $data
     *
     * @return ProjectModel[]
     *
     * @throws InvalidParameterException
     */
    private function validateJoinedProjects(array $data): array
    {
        $projects = $this->validateArrayParameter(
            $data,
            UserModelInterface::PROPERTY_JOINED_PROJECTS,
            false,
            true
        );

        return \is_array($projects)
            ? \array_map(
                function ($project) {
                    if ($project instanceof ProjectModel) {
                        return $project;
                    }

                    if (\is_array($project)) {
                        return $this->getProjectModelFactory()->create($project);
                    }

                    throw new InvalidParameterException();
                },
                $projects
            )
            : [];
    }
}
