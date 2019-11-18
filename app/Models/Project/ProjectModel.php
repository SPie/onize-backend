<?php

namespace App\Models\Project;

use App\Models\ModelInterface;
use App\Models\SoftDeletable;
use App\Models\Timestampable;
use App\Models\User\UserModelInterface;
use App\Models\Uuidable;
use Illuminate\Support\Collection;

/**
 * Interface ProjectModel
 *
 * @package App\Models\Project
 */
interface ProjectModel extends ModelInterface, Timestampable, SoftDeletable, Uuidable
{
    const PROPERTY_LABEL                      = 'label';
    const PROPERTY_USER                       = 'user';
    const PROPERTY_DESCRIPTION                = 'description';
    const PROPERTY_PROJECT_INVITES            = 'projectInvites';
    const PROPERTY_MEMBERS                    = 'members';
    const PROPERTY_PROJECT_META_DATA_ELEMENTS = 'projectMetaDataElements';

    /**
     * @param string $label
     *
     * @return ProjectModel
     */
    public function setLabel(string $label): self;

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param UserModelInterface $user
     *
     * @return ProjectModel
     */
    public function setUser(UserModelInterface $user): self;

    /**
     * @return UserModelInterface
     */
    public function getUser(): UserModelInterface;

    /**
     * @param string|null $description
     *
     * @return ProjectModel
     */
    public function setDescription(?string $description): self;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @param UserModelInterface[] $members
     *
     * @return $this
     */
    public function setMembers(array $members): self;

    /**
     * @param UserModelInterface $member
     *
     * @return $this
     */
    public function addMember(UserModelInterface $member): self;

    /**
     * @return UserModelInterface[]|Collection
     */
    public function getMembers(): Collection;

    /**
     * @param string $email
     *
     * @return bool
     */
    public function hasMemberWithEmail(string $email): bool;

    /**
     * @param ProjectInviteModel[] $projectInvites
     *
     * @return $this
     */
    public function setProjectInvites(array $projectInvites): self;

    /**
     * @param ProjectInviteModel $projectInvite
     *
     * @return $this
     */
    public function addProjectInvite(ProjectInviteModel $projectInvite): self;

    /**
     * @return ProjectInviteModel[]|Collection
     */
    public function getProjectInvites(): Collection;

    /**
     * @param ProjectMetaDataElementModel[] $metaDataElements
     *
     * @return $this
     */
    public function setProjectMetaDataElements(array $metaDataElements): self;

    /**
     * @param ProjectMetaDataElementModel $metaDataElement
     *
     * @return $this
     */
    public function addMetaDataElement(ProjectMetaDataElementModel $metaDataElement): self;

    /**
     * @return ProjectMetaDataElementModel[]|Collection
     */
    public function getProjectMetaDataElements(): Collection;
}
