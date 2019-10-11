<?php

namespace App\Models\Project;

use App\Models\AbstractDoctrineModel;
use App\Models\SoftDelete;
use App\Models\Timestamps;
use App\Models\User\UserModelInterface;
use App\Models\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Illuminate\Support\Collection;

/**
 * Class ProjectDoctrineModel
 *
 * @ORM\Table(name="projects")
 * @ORM\Entity(repositoryClass="App\Repositories\Project\ProjectDoctrineRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 *
 * @package App\Models\Project
 */
class ProjectDoctrineModel extends AbstractDoctrineModel implements ProjectModel
{
    use SoftDelete;
    use Timestamps;
    use Uuid;

    /**
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $label;

    /**
     * @ORM\ManyToOne(targetEntity="App\Models\User\UserDoctrineModel", inversedBy="projects", cascade={"persist"})
     *
     * @var UserModelInterface
     */
    private $user;

    /**
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     *
     * @var string|null
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Models\Project\ProjectInviteDoctrineModel", mappedBy="project", cascade={"persist"})
     *
     * @var ArrayCollection|ProjectInviteModel[]
     */
    private $projectInvites;

    /**
     * ProjectDoctrineModel constructor.
     *
     * @param string               $uuid
     * @param string               $label
     * @param UserModelInterface   $user
     * @param string|null          $description
     * @param \DateTime|null       $createdAt
     * @param \DateTime|null       $updatedAt
     * @param \DateTime|null       $deletedAt
     * @param ProjectInviteModel[] $projectInvites
     */
    public function __construct(
        string $uuid,
        string $label,
        UserModelInterface $user,
        string $description = null,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null,
        \DateTime $deletedAt = null,
        array $projectInvites = []
    ) {
        $this->uuid = $uuid;
        $this->label = $label;
        $this->user = $user;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
        $this->projectInvites = new ArrayCollection($projectInvites);
    }

    /**
     * @param string $label
     *
     * @return ProjectModel
     */
    public function setLabel(string $label): ProjectModel
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param UserModelInterface $user
     *
     * @return ProjectModel
     */
    public function setUser(UserModelInterface $user): ProjectModel
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return UserModelInterface
     */
    public function getUser(): UserModelInterface
    {
        return $this->user;
    }

    /**
     * @param string|null $description
     *
     * @return ProjectModel
     */
    public function setDescription(?string $description): ProjectModel
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param ProjectInviteModel[] $projectInvites
     *
     * @return $this
     */
    public function setProjectInvites(array $projectInvites): ProjectModel
    {
        $this->projectInvites = new ArrayCollection($projectInvites);

        return $this;
    }

    /**
     * @param ProjectInviteModel $projectInvite
     *
     * @return $this
     */
    public function addProjectInvite(ProjectInviteModel $projectInvite): ProjectModel
    {
        if (!$this->projectInvites->contains($projectInvite)) {
            $this->projectInvites->add($projectInvite);
        }

        return $this;
    }

    /**
     * @return ProjectInviteModel[]|Collection
     */
    public function getProjectInvites(): Collection
    {
        return new Collection($this->projectInvites->toArray());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::PROPERTY_UUID        => $this->getUuid(),
            self::PROPERTY_LABEL       => $this->getLabel(),
            self::PROPERTY_USER        => $this->getUser()->toArray(),
            self::PROPERTY_DESCRIPTION => $this->getDescription(),
            self::PROPERTY_CREATED_AT  => $this->getCreatedAt()
                ? (array)new \DateTime($this->getCreatedAt()->format('Y-m-d H:i:s'))
                : null,
            self::PROPERTY_UPDATED_AT  => $this->getUpdatedAt()
                ? (array)new \DateTime($this->getUpdatedAt()->format('Y-m-d H:i:s'))
                : null,
            self::PROPERTY_DELETED_AT  => $this->getDeletedAt()
                ? (array)new \DateTime($this->getDeletedAt()->format('Y-m-d H:i:s'))
                : null,
        ];
    }
}
