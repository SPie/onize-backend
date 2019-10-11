<?php

namespace App\Models\Project;

use App\Models\ModelInterface;
use App\Models\Timestampable;
use App\Models\Uuidable;

/**
 * Interface ProjectInviteModel
 *
 * @package App\Models\Project
 */
interface ProjectInviteModel extends ModelInterface, Timestampable, Uuidable
{
    const PROPERTY_TOKEN = 'token';
    const PROPERTY_EMAIL = 'email';
    const PROPERTY_PROJECT = 'project';

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken(string $token): self;

    /**
     * @return string
     */
    public function getToken(): string;

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email): self;

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @param ProjectModel $project
     *
     * @return $this
     */
    public function setProject(ProjectModel $project): self;

    /**
     * @return ProjectModel
     */
    public function getProject(): ProjectModel;
}
