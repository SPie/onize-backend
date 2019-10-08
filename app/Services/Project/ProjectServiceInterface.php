<?php

namespace App\Services\Project;

use App\Models\Project\ProjectInviteModel;
use App\Models\Project\ProjectModel;
use App\Models\User\UserModelInterface;
use App\Services\User\UsersServiceInterface;

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
     * @param string             $uuid
     * @param UserModelInterface $authenticatedUser
     *
     * @return $this
     */
    public function removeProject(string $uuid, UserModelInterface $authenticatedUser): self;

    /**
     * @param string                $uuid
     * @param string                $email
     * @param UsersServiceInterface $usersService
     *
     * @return ProjectInviteModel
     */
    public function invite(string $uuid, string $email, UsersServiceInterface $usersService): ProjectInviteModel;
}
