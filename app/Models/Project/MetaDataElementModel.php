<?php

namespace App\Models\Project;

use App\Models\ModelInterface;

/**
 * Interface MetaDataElementModel
 *
 * @package App\Models\Project
 */
interface MetaDataElementModel extends ModelInterface
{
    const PROPERTY_NAME     = 'name';
    const PROPERTY_PROJECT  = 'project';
    const PROPERTY_REQUIRED = 'required';
    const PROPERTY_IN_LIST  = 'inList';
    const PROPERTY_POSITION = 'position';

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * @return string
     */
    public function getName(): string;

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

    /**
     * @param bool $required
     *
     * @return $this
     */
    public function setRequired(bool $required): self;

    /**
     * @return bool
     */
    public function isRequired(): bool;

    /**
     * @param bool $inList
     *
     * @return $this
     */
    public function setInList(bool $inList): self;

    /**
     * @return bool
     */
    public function isInList(): bool;

    /**
     * @param int $position
     *
     * @return $this
     */
    public function setPosition(int $position): self;

    /**
     * @return int
     */
    public function getPosition(): int;
}
