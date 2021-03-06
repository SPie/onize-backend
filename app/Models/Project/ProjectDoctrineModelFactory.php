<?php

namespace App\Models\Project;

use App\Exceptions\InvalidParameterException;
use App\Models\ModelInterface;
use App\Models\ModelParameterValidation;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Models\UuidCreate;
use App\Services\Uuid\UuidFactory;

/**
 * Class ProjectDoctrineModelFactory
 *
 * @package App\Models\Project
 */
final class ProjectDoctrineModelFactory implements ProjectModelFactory
{
    use ModelParameterValidation;
    use UuidCreate;

    /**
     * @var UserModelFactoryInterface
     */
    private $userModelFactory;

    /**
     * @var ProjectInviteModelFactory
     */
    private $projectInviteModelFactory;

    /**
     * @var ProjectMetaDataElementModelFactory
     */
    private $metaDataElementModelFactory;

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
     * @param ProjectInviteModelFactory $projectInviteModelFactory
     *
     * @return ProjectModelFactory
     */
    public function setProjectInviteModelFactory(ProjectInviteModelFactory $projectInviteModelFactory): ProjectModelFactory
    {
        $this->projectInviteModelFactory =  $projectInviteModelFactory;

        return $this;
    }

    /**
     * @return ProjectInviteModelFactory
     */
    private function getProjectInviteModelFactory(): ProjectInviteModelFactory
    {
        return $this->projectInviteModelFactory;
    }

    /**
     * @param ProjectMetaDataElementModelFactory $metaDataElementModelFactory
     *
     * @return ProjectModelFactory
     */
    public function setProjectMetaDataElementModelFactory(
        ProjectMetaDataElementModelFactory $metaDataElementModelFactory
    ): ProjectModelFactory {
        $this->metaDataElementModelFactory = $metaDataElementModelFactory;

        return $this;
    }

    /**
     * @return ProjectMetaDataElementModelFactory
     */
    private function getMetaDataElementModelFactory(): ProjectMetaDataElementModelFactory
    {
        return $this->metaDataElementModelFactory;
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
            $this->validateStringParameter($data, ProjectModel::PROPERTY_DESCRIPTION, false, true),
            $this->validateDateTimeParameter($data, ProjectModel::PROPERTY_CREATED_AT, false),
            $this->validateDateTimeParameter($data, ProjectModel::PROPERTY_UPDATED_AT, false),
            $this->validateDateTimeParameter($data, ProjectModel::PROPERTY_DELETED_AT, false),
            $this->validateProjectInvites($data),
            $this->validateMembers($data),
            $this->validateMetaDataElements($data)
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

        $projectInvites = $this->validateProjectInvites($data);
        if (!empty($projectInvites)) {
            $model->setProjectInvites($projectInvites);
        }

        $members = $this->validateMembers($data);
        if (!empty($members)) {
            $model->setMembers($members);
        }

        $metaDataElements = $this->validateMetaDataElements($data);
        if (!empty($metaDataElements)) {
            $model->setProjectMetaDataElements($metaDataElements);
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

    /**
     * @param array $data
     *
     * @return ProjectModel[]
     *
     * @throws InvalidParameterException
     */
    private function validateProjectInvites(array $data): array
    {
        $projectInvites = $this->validateArrayParameter(
            $data,
            ProjectModel::PROPERTY_PROJECT_INVITES,
            false,
            true
        );

        return \is_array($projectInvites)
            ? \array_map(
                function ($projectInvite) {
                    if ($projectInvite instanceof ProjectInviteModel) {
                        return $projectInvite;
                    }

                    if (\is_array($projectInvite)) {
                        return $this->getProjectInviteModelFactory()->create($projectInvite);
                    }

                    throw new InvalidParameterException();
                },
                $projectInvites
            )
            : [];
    }

    /**
     * @param array $data
     *
     * @return UserModelInterface[]
     *
     * @throws InvalidParameterException
     */
    private function validateMembers(array $data): array
    {
        $members = $this->validateArrayParameter(
            $data,
            ProjectModel::PROPERTY_MEMBERS,
            false,
            true
        );

        return \is_array($members)
            ? \array_map(
                function ($member) {
                    if ($member instanceof UserModelInterface) {
                        return $member;
                    }

                    if (\is_array($member)) {
                        return $this->getUserModelFactory()->create($member);
                    }

                    throw new InvalidParameterException();
                },
                $members
            )
            : [];
    }

    /**
     * @param array $data
     *
     * @return ProjectMetaDataElementModel[]
     *
     * @throws InvalidParameterException
     */
    private function validateMetaDataElements(array $data): array
    {
        $metaDataElements = $this->validateArrayParameter(
            $data,
            ProjectModel::PROPERTY_PROJECT_META_DATA_ELEMENTS,
            false,
            true
        );

        return \is_array($metaDataElements)
            ? \array_map(
                function ($metaDataElement) {
                    if ($metaDataElement instanceof ProjectMetaDataElementModel) {
                        return $metaDataElement;
                    }

                    if (\is_array($metaDataElement)) {
                        return $this->getMetaDataElementModelFactory()->create($metaDataElement);
                    }

                    throw new InvalidParameterException();
                },
                $metaDataElements
            )
            : [];
    }
}
