<?php

namespace App\Repositories\Project;

use App\Models\Project\ProjectInviteModel;
use App\Models\Project\ProjectModel;
use App\Repositories\RepositoryInterface;

/**
 * Interface ProjectInviteRepository
 *
 * @package App\Repositories\Project
 */
interface ProjectInviteRepository extends RepositoryInterface
{
    /**
     * @param string       $email
     * @param ProjectModel $project
     *
     * @return ProjectInviteModel|null
     */
    public function findByEmailAndProject(string $email, ProjectModel $project): ?ProjectInviteModel;
}
