<?php

namespace App\Repositories\Project;

use App\Models\ModelInterface;
use App\Models\Project\ProjectModel;
use App\Repositories\RepositoryInterface;

/**
 * Interface ProjectRepository
 *
 * @package App\Repositories\Project
 */
interface ProjectRepository extends RepositoryInterface
{
    /**
     * @param ProjectRepository|ModelInterface $model
     * @param bool                             $flush
     *
     * @return ProjectModel|ModelInterface
     */
    public function save(ModelInterface $model, bool $flush = true): ModelInterface;
}
