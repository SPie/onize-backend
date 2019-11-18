<?php

namespace App\Models\Project;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;
use App\Models\User\UserModelFactoryInterface;

/**
 * Interface ProjectModelFactory
 *
 * @package App\Models\Project
 */
interface ProjectModelFactory extends ModelFactoryInterface
{
    /**
     * @param UserModelFactoryInterface $userModelFactory
     *
     * @return ProjectModelFactory
     */
    public function setUserModelFactory(UserModelFactoryInterface $userModelFactory): self;

    /**
     * @param ProjectInviteModelFactory $projectInviteModelFactory
     *
     * @return $this
     */
    public function setProjectInviteModelFactory(ProjectInviteModelFactory $projectInviteModelFactory): self;

    /**
     * @param ProjectMetaDataElementModelFactory $metaDataElementModelFactory
     *
     * @return $this
     */
    public function setProjectMetaDataElementModelFactory(ProjectMetaDataElementModelFactory $metaDataElementModelFactory): self;

    /**
     * @param array $data
     *
     * @return ProjectModel|ModelInterface
     */
    public function create(array $data): ModelInterface;

    /**
     * @param ProjectModel|ModelInterface $model
     * @param array                       $data
     *
     * @return ProjectModel|ModelInterface
     */
    public function fill(ModelInterface $model, array $data): ModelInterface;
}
