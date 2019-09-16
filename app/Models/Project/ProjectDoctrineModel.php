<?php

namespace App\Models\Project;

use App\Models\AbstractDoctrineModel;
use App\Models\SoftDelete;
use App\Models\Timestamps;
use App\Models\User\UserModelInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ProjectDoctrineModel
 *
 * @ORM\Table(name="projects")
 * @ORM\Entity(repositoryClass="App\Repositories\Project\ProjectDoctrineRepository")
 *
 * @package App\Models\Project
 */
final class ProjectDoctrineModel extends AbstractDoctrineModel implements ProjectModel
{
    use SoftDelete;
    use Timestamps;

    /**
     * @ORM\Column(name="identifier", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $identifier;

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
     * ProjectDoctrineModel constructor.
     *
     * @param string             $identifier
     * @param string             $label
     * @param UserModelInterface $user
     * @param string|null        $description
     * @param \DateTime|null     $createdAt
     * @param \DateTime|null     $updatedAt
     * @param \DateTime|null     $deletedAt
     */
    public function __construct(
        string $identifier,
        string $label,
        UserModelInterface $user,
        string $description = null,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null,
        \DateTime $deletedAt = null
    ){
        $this->identifier = $identifier;
        $this->label = $label;
        $this->user = $user;
        $this->description = $description;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }

    /**
     * @param string $identifier
     *
     * @return ProjectModel
     */
    public function setIdentifier(string $identifier): ProjectModel
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
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
}
