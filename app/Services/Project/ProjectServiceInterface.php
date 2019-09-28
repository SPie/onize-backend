<?php

namespace App\Services\Project;

use App\Exceptions\ModelNotFoundException;
use App\Models\Project\ProjectModel;
use App\Models\User\UserModelInterface;

/**
 * Interface ProjectServiceInterface
 *
 * @package App\Services\Project
 */
interface ProjectServiceInterface
{
    /**
     * @param array              $projectData
     * @param UserModelInterface $user
     *
     * @return ProjectModel
     */
    public function createProject(array $projectData, UserModelInterface $user): ProjectModel;

    /**
     * @param string $uuid
     *
     * @return $this
     *
     * @throws ModelNotFoundException
     */
    public function removeProject(string $uuid): self;
}
