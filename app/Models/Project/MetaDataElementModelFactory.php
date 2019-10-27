<?php

namespace App\Models\Project;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;

/**
 * Interface MetaDataElementModelFactory
 *
 * @package App\Models\Project
 */
interface MetaDataElementModelFactory extends ModelFactoryInterface
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
     * @return MetaDataElementModel|ModelInterface
     */
    public function create(array $data): ModelInterface;

    /**
     * @param MetaDataElementModel|ModelInterface $model
     * @param array                               $data
     *
     * @return MetaDataElementModel|ModelInterface
     */
    public function fill(ModelInterface $model, array $data): ModelInterface;
}
