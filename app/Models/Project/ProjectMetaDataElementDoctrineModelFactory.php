<?php

namespace App\Models\Project;

use App\Exceptions\InvalidParameterException;
use App\Models\ModelInterface;
use App\Models\ModelParameterValidation;

/**
 * Class MetaDataElementDoctrineModelFactory
 *
 * @package App\Models\Project
 */
final class ProjectMetaDataElementDoctrineModelFactory implements ProjectMetaDataElementModelFactory
{
    use ModelParameterValidation;

    /**
     * @var ProjectModelFactory
     */
    private $projectModelFactory;

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
            $this->validateStringParameter($data, ProjectMetaDataElementModel::PROPERTY_NAME),
            $this->validateStringParameter($data, ProjectMetaDataElementModel::PROPERTY_LABEL),
            $this->validateProjectModel($data),
            $this->validateBooleanParameter($data, ProjectMetaDataElementModel::PROPERTY_REQUIRED),
            $this->validateBooleanParameter($data, ProjectMetaDataElementModel::PROPERTY_IN_LIST),
            $this->validateIntegerParameter($data, ProjectMetaDataElementModel::PROPERTY_POSITION)
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
        $name = $this->validateStringParameter($data, ProjectMetaDataElementModel::PROPERTY_NAME, false);
        if (!empty($name)) {
            $model->setName($name);
        }

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
