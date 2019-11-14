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
final class MetaDataElementDoctrineModelFactory implements MetaDataElementModelFactory
{
    use ModelParameterValidation;

    /**
     * @var ProjectModelFactory
     */
    private $projectModelFactory;

    /**
     * @param ProjectModelFactory $projectModelFactory
     *
     * @return MetaDataElementModelFactory
     */
    public function setProjectModelFactory(ProjectModelFactory $projectModelFactory): MetaDataElementModelFactory
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
     * @return MetaDataElementModel|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function create(array $data): ModelInterface
    {
        return (new MetaDataElementDoctrineModel(
            $this->validateStringParameter($data, MetaDataElementModel::PROPERTY_NAME),
            $this->validateStringParameter($data, MetaDataElementModel::PROPERTY_LABEL),
            $this->validateProjectModel($data),
            $this->validateBooleanParameter($data, MetaDataElementModel::PROPERTY_REQUIRED),
            $this->validateBooleanParameter($data, MetaDataElementModel::PROPERTY_IN_LIST),
            $this->validateIntegerParameter($data, MetaDataElementModel::PROPERTY_POSITION)
        ))->setId($data[MetaDataElementModel::PROPERTY_ID] ?? null);
    }

    /**
     * @param MetaDataElementModel|ModelInterface $model
     * @param array          $data
     *
     * @return MetaDataElementModel|ModelInterface
     *
     * @throws InvalidParameterException
     */
    public function fill(ModelInterface $model, array $data): ModelInterface
    {
        $name = $this->validateStringParameter($data, MetaDataElementModel::PROPERTY_NAME, false);
        if (!empty($name)) {
            $model->setName($name);
        }

        $label = $this->validateStringParameter($data, MetaDataElementModel::PROPERTY_LABEL, false);
        if (!empty($label)) {
            $model->setLabel($label);
        }

        $project = $this->validateProjectModel($data, false);
        if (!empty($project)) {
            $model->setProject($project);
        }

        $required = $this->validateBooleanParameter($data, MetaDataElementModel::PROPERTY_REQUIRED, false);
        if ($required !== null) {
            $model->setRequired($required);
        }

        $inList = $this->validateBooleanParameter($data, MetaDataElementModel::PROPERTY_IN_LIST, false);
        if ($inList !== null) {
            $model->setInList($inList);
        }

        $position = $this->validateIntegerParameter($data, MetaDataElementModel::PROPERTY_POSITION, false);
        if (!empty($position)) {
            $model->setPosition($position);
        }

        $id = $this->validateIntegerParameter($data, MetaDataElementModel::PROPERTY_ID, false);
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
            MetaDataElementModel::PROPERTY_PROJECT,
            $this->getProjectModelFactory(),
            ProjectModel::class,
            $required
        );
    }
}
