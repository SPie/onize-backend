<?php

namespace App\Models\Project;

use App\Models\ModelInterface;
use App\Models\SoftDeletable;
use App\Models\Timestampable;
use App\Models\User\UserModelInterface;

/**
 * Interface ProjectModel
 *
 * @package App\Models\Project
 */
interface ProjectModel extends ModelInterface, Timestampable, SoftDeletable
{
    const PROPERTY_IDENTIFIER  = 'identifier';
    const PROPERTY_LABEL       = 'label';
    const PROPERTY_USER        = 'user';
    const PROPERTY_DESCRIPTION = 'description';

    /**
     * @param string $identifier
     *
     * @return ProjectModel
     */
    public function setIdentifier(string $identifier): ProjectModel;

    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @param string $label
     *
     * @return ProjectModel
     */
    public function setLabel(string $label): ProjectModel;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param UserModelInterface $user
     *
     * @return ProjectModel
     */
    public function setUser(UserModelInterface $user): ProjectModel;

    /**
     * @return UserModelInterface
     */
    public function getUser(): UserModelInterface;

    /**
     * @param string|null $description
     *
     * @return ProjectModel
     */
    public function setDescription(?string $description): ProjectModel;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;
}
