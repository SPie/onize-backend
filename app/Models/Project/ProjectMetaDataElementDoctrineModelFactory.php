<?php

namespace App\Models\Project;

use App\Exceptions\InvalidParameterException;
use App\Models\ModelInterface;
use App\Models\ModelParameterValidation;
use App\Models\UuidCreate;
use App\Services\Uuid\UuidFactory;

/**
 * Class MetaDataElementDoctrineModelFactory
 *
 * @package App\Models\Project
 */
final class ProjectMetaDataElementDoctrineModelFactory implements ProjectMetaDataElementModelFactory
{
    use ModelParameterValidation;
    use UuidCreate;

    /**
     * @var ProjectModelFactory
     */
    private $projectModelFactory;

    /**
     * ProjectMetaDataElementDoctrineModelFactory constructor.
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
     * @return ProjectMetaDataElementModelFactory
     */
    public function setProjectModelFactory(ProjectModelFactory $projectModelFactory): ProjectMetaDataElementModelFactory
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
     * @return ProjectMetaDataElementModel|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function create(array $data): ModelInterface
    {
        return (new ProjectMetaDataElementDoctrineModel(
            $this->getUuidFactory()->create(),
            $this->validateStringParameter($data, ProjectMetaDataElementModel::PROPERTY_LABEL),
            $this->validateProjectModel($data),
            $this->validateBooleanParameter($data, ProjectMetaDataElementModel::PROPERTY_REQUIRED),
            $this->validateBooleanParameter($data, ProjectMetaDataElementModel::PROPERTY_IN_LIST),
            $this->validateIntegerParameter($data, ProjectMetaDataElementModel::PROPERTY_POSITION),
            $this->validateEnumParameter(
                $data,
                ProjectMetaDataElementModel::PROPERTY_FIELD_TYPE,
                [
                    ProjectMetaDataElementModel::FIELD_TYPE_TEXT,
                    ProjectMetaDataElementModel::FIELD_TYPE_NUMBER,
                    ProjectMetaDataElementModel::FIELD_TYPE_DATE,
                    ProjectMetaDataElementModel::FIELD_TYPE_EMAIL,
                ]
            )
        ))->setId($data[ProjectMetaDataElementModel::PROPERTY_ID] ?? null);
    }

    /**
     * @param ProjectMetaDataElementModel|ModelInterface $model
     * @param array                                      $data
     *
     * @return ProjectMetaDataElementModel|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function fill(ModelInterface $model, array $data): ModelInterface
    {
        $label = $this->validateStringParameter($data, ProjectMetaDataElementModel::PROPERTY_LABEL, false);
        if (!empty($label)) {
            $model->setLabel($label);
        }

        $project = $this->validateProjectModel($data, false);
        if (!empty($project)) {
            $model->setProject($project);
        }

        $required = $this->validateBooleanParameter($data, ProjectMetaDataElementModel::PROPERTY_REQUIRED, false);
        if ($required !== null) {
            $model->setRequired($required);
        }

        $inList = $this->validateBooleanParameter($data, ProjectMetaDataElementModel::PROPERTY_IN_LIST, false);
        if ($inList !== null) {
            $model->setInList($inList);
        }

        $position = $this->validateIntegerParameter($data, ProjectMetaDataElementModel::PROPERTY_POSITION, false);
        if (!empty($position)) {
            $model->setPosition($position);
        }

        $fieldType = $this->validateEnumParameter(
            $data,
            ProjectMetaDataElementModel::PROPERTY_FIELD_TYPE,
            [
                ProjectMetaDataElementModel::FIELD_TYPE_TEXT,
                ProjectMetaDataElementModel::FIELD_TYPE_NUMBER,
                ProjectMetaDataElementModel::FIELD_TYPE_DATE,
                ProjectMetaDataElementModel::FIELD_TYPE_EMAIL,
            ],
            false
        );
        if (!empty($fieldType)) {
            $model->setFieldType($fieldType);
        }

        $id = $this->validateIntegerParameter($data, ProjectMetaDataElementModel::PROPERTY_ID, false);
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
     *
     * @throws InvalidParameterException
     */
    private function validateProjectModel(array $data, bool $required = true): ?ProjectModel
    {
        return $this->validateModelParameter(
            $data,
            ProjectMetaDataElementModel::PROPERTY_PROJECT,
            $this->getProjectModelFactory(),
            ProjectModel::class,
            $required
        );
    }
}
