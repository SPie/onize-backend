<?php

namespace App\Models\Project;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;

/**
 * Interface ProjectInviteModelFactory
 *
 * @package App\Models\Project
 */
interface ProjectInviteModelFactory extends ModelFactoryInterface
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
     * @return ProjectInviteModel|ModelInterface
     */
    public function create(array $data): ModelInterface;

    /**
     * @param ProjectInviteModel|ModelInterface $model
     * @param array                             $data
     *
     * @return ProjectInviteModel|ModelInterface
     */
    public function fill(ModelInterface $model, array $data): ModelInterface;
}
