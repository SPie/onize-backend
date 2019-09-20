<?php

namespace App\Models\User;

use App\Models\AbstractDoctrineModel;
use App\Models\Authenticate;
use App\Models\Project\ProjectModel;
use App\Models\SoftDelete;
use App\Models\Timestamps;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use SPie\LaravelJWT\Contracts\RefreshToken;

/**
 * Class UserDoctrineModel
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repositories\User\UserDoctrineRepository")
 *
 * @package App\Models\User
 */
class UserDoctrineModel extends AbstractDoctrineModel implements UserModelInterface
{
    use Authenticate;
    use Timestamps;
    use SoftDelete;

    /**
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="App\Models\User\RefreshTokenDoctrineModel", mappedBy="user", cascade={"persist"})
     *
     * @var RefreshTokenModel[]|ArrayCollection
     */
    private $refreshTokens;

    /**
     * @ORM\OneToMany(targetEntity="App\Models\Project\ProjectDoctrineModel", mappedBy="user", cascade={"persist"})
     *
     * @var ProjectModel[]|ArrayCollection
     */
    private $projects;

    /**
     * UserDoctrineModel constructor.
     *
     * @param string         $email
     * @param string         $password
     * @param RefreshToken[] $refreshTokens
     * @param ProjectModel[] $projects
     * @param \DateTime|null $createdAt
     * @param \DateTime|null $updatedAt
     * @param \DateTime|null $deletedAt
     */
    public function __construct(
        string $email,
        string $password,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null,
        \DateTime $deletedAt = null,
        array $refreshTokens = [],
        array $projects = []
    ) {
        $this->email = $email;
        $this->password = Hash::make($password);
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
        $this->refreshTokens = new ArrayCollection($refreshTokens);
        $this->projects = new ArrayCollection($projects);
    }

    /**
     * @param string $email
     *
     * @return UserModelInterface
     */
    public function setEmail(string $email): UserModelInterface
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
     * @param array $refreshTokens
     *
     * @return UserModelInterface
     */
    public function setRefreshTokens(array $refreshTokens): UserModelInterface
    {
        $this->refreshTokens = new ArrayCollection($refreshTokens);

        return $this;
    }

    /**
     * @param RefreshTokenModel $refreshToken
     *
     * @return UserModelInterface
     */
    public function addRefreshToken(RefreshTokenModel $refreshToken): UserModelInterface
    {
        if (!$this->refreshTokens->contains($refreshToken)) {
            $this->refreshTokens->add($refreshToken);
        }

        return $this;
    }

    /**
     * @return RefreshTokenModel[]|Collection
     */
    public function getRefreshTokens(): Collection
    {
        return new Collection($this->refreshTokens->toArray());
    }

    /**
     * @param ProjectModel[] $projects
     *
     * @return UserModelInterface
     */
    public function setProjects(array $projects): UserModelInterface
    {
        $this->projects = new ArrayCollection($projects);

        return $this;
    }

    /**
     * @param ProjectModel $project
     *
     * @return UserModelInterface
     */
    public function addProject(ProjectModel $project): UserModelInterface
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
        }

        return $this;
    }

    /**
     * @return ProjectModel[]|Collection
     */
    public function getProjects(): Collection
    {
        return new Collection($this->projects->toArray());
    }

    /**
     * @return int
     */
    public function getJWTIdentifier(): int
    {
        return $this->getId();
    }

    /**
     * @return array
     */
    public function getCustomClaims(): array
    {
        return [
            self::PROPERTY_EMAIL => $this->getEmail(),
        ];
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return \array_merge(
            parent::toArray(),
            [
                self::PROPERTY_EMAIL      => $this->getEmail(),
                self::PROPERTY_CREATED_AT => $this->getCreatedAt()
                    ? (array)new \DateTime($this->getCreatedAt()->format('Y-m-d H:i:s'))
                    : null,
                self::PROPERTY_UPDATED_AT => $this->getUpdatedAt()
                    ? (array)new \DateTime($this->getUpdatedAt()->format('Y-m-d H:i:s'))
                    : null,
                self::PROPERTY_DELETED_AT => $this->getDeletedAt()
                    ? (array)new \DateTime($this->getDeletedAt()->format('Y-m-d H:i:s'))
                    : null,
            ]
        );
    }
}
