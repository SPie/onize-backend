<?php

namespace App\Models\Project;

use App\Models\ModelInterface;
use App\Models\Uuidable;

/**
 * Interface ProjectMetaDataElementModel
 *
 * @package App\Models\Project
 */
interface ProjectMetaDataElementModel extends ModelInterface, Uuidable
{
    const PROPERTY_LABEL      = 'label';
    const PROPERTY_PROJECT    = 'project';
    const PROPERTY_REQUIRED   = 'required';
    const PROPERTY_IN_LIST    = 'inList';
    const PROPERTY_POSITION   = 'position';
    const PROPERTY_FIELD_TYPE = 'fieldType';

    const FIELD_TYPE_TEXT   = 'text';
    const FIELD_TYPE_NUMBER = 'number';
    const FIELD_TYPE_DATE   = 'date';
    const FIELD_TYPE_EMAIL  = 'email';

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel(string $label): self;

    /**
     * @return string
     */
    public function getLabel(): string;

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

    /**
     * @param string $fieldType
     *
     * @return $this
     */
    public function setFieldType(string $fieldType): self;

    /**
     * @return string
     */
    public function getFieldType(): string;
}
