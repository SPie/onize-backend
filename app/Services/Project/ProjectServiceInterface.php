<?php

namespace App\Services\Project;

use App\Models\Project\MetaDataElementModel;
use App\Models\Project\ProjectInviteModel;
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
     * @param string $uuid
     *
     * @return ProjectModel
     */
    public function getProject(string $uuid): ProjectModel;

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
     * @param string $uuid
     * @param string $email
     *
     * @return ProjectInviteModel
     */
    public function invite(string $uuid, string $email): ProjectInviteModel;

    /**
     * @param string $uuid
     * @param array  $metaDataElements
     *
     * @return MetaDataElementModel[]
     */
    public function createMetaDataElements(string $uuid, array $metaDataElements): array;
}
