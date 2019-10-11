<?php

namespace App\Models\Project;

use App\Models\AbstractDoctrineModel;
use App\Models\Timestamps;
use App\Models\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ProjectInviteDoctrineModel
 *
 * @ORM\Table(name="project_invites")
 * @ORM\Entity(repositoryClass="App\Repositories\Project\ProjectInviteDoctrineRepository")
 *
 * @package App\Models\Project
 */
class ProjectInviteDoctrineModel extends AbstractDoctrineModel implements ProjectInviteModel
{
    use Timestamps;
    use Uuid;

    /**
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $token;

    /**
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="App\Models\Project\ProjectDoctrineModel", inversedBy="invites", cascade={"persist"})
     *
     * @var ProjectModel
     */
    private $project;

    /**
     * ProjectInviteDoctrineModel constructor.
     *
     * @param string         $uuid
     * @param string         $token
     * @param string         $email
     * @param ProjectModel   $project
     * @param \DateTime|null $createdAt
     * @param \DateTime|null $updatedAt
     */
    public function __construct(
        string $uuid,
        string $token,
        string $email,
        ProjectModel $project,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null
    ) {
        $this->uuid = $uuid;
        $this->token = $token;
        $this->email = $email;
        $this->project = $project;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken(string $token): ProjectInviteModel
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email): ProjectInviteModel
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param ProjectModel $project
     *
     * @return $this
     */
    public function setProject(ProjectModel $project): ProjectInviteModel
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return ProjectModel
     */
    public function getProject(): ProjectModel
    {
        return $this->project;
    }
}
