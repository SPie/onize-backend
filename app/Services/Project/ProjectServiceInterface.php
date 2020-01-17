<?php

namespace App\Services\Project;

use App\Exceptions\ModelNotFoundException;
use App\Models\Project\ProjectMetaDataElementModel;
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
     *
     * @return ProjectMetaDataElementModel
     *
     * @throws ModelNotFoundException
     */
    public function getMetaDataElement(string $uuid): ProjectMetaDataElementModel;

    /**
     * @param string $uuid
     * @param array  $metaDataElements
     *
     * @return ProjectMetaDataElementModel[]
     */
    public function createMetaDataElements(string $uuid, array $metaDataElements): array;

    /**
     * @param array $metaDataElementsData
     *
     * @return ProjectMetaDataElementModel[]
     */
    public function updateMetaDataElements(array $metaDataElementsData): array;

    /**
     * @param string $uuid
     *
     * @return $this
     */
    public function removeProjectMetaDataElement(string $uuid): self;
}
