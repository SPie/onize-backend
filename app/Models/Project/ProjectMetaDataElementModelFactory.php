<?php

namespace App\Models\Project;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;

/**
 * Interface ProjectMetaDataElementModelFactory
 *
 * @package App\Models\Project
 */
interface ProjectMetaDataElementModelFactory extends ModelFactoryInterface
{
    /**
     * @param ProjectModelFactory $projectModelFactory
     *
     * @return $this
     */
    public function setProjectModelFactory(ProjectModelFactory $projectModelFactory): self;

    /**
     * @param array $data
     *
     * @return ProjectMetaDataElementModel|ModelInterface
     */
    public function create(array $data): ModelInterface;

    /**
     * @param ProjectMetaDataElementModel|ModelInterface $model
     * @param array                                      $data
     *
     * @return ProjectMetaDataElementModel|ModelInterface
     */
    public function fill(ModelInterface $model, array $data): ModelInterface;
}
