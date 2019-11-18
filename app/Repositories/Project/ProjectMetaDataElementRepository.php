<?php

namespace App\Repositories\Project;

use App\Models\ModelInterface;
use App\Models\Project\ProjectMetaDataElementModel;
use App\Models\Project\ProjectModel;
use App\Repositories\RepositoryInterface;

/**
 * Interface ProjectMetaDataElementRepository
 *
 * @package App\Repositories\Project
 */
interface ProjectMetaDataElementRepository extends RepositoryInterface
{
    /**
     * @param ProjectMetaDataElementModel|ModelInterface $model
     * @param bool                                       $flush
     *
     * @return ProjectMetaDataElementModel|ModelInterface
     */
    public function save(ModelInterface $model, bool $flush = true): ModelInterface;

    /**
     * @param string       $name
     * @param ProjectModel $project
     *
     * @return ProjectMetaDataElementModel|null
     */
    public function findByNameAndProject(string $name, ProjectModel $project): ?ProjectMetaDataElementModel;
}
