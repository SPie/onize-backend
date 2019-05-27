<?php

namespace App\Models\User;

use App\Models\AbstractDoctrineModel;
use App\Models\Auth\RefreshTokenModel;
use App\Models\Authenticate;
use App\Models\SoftDelete;
use App\Models\Timestamps;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

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
     * @ORM\OneToMany(targetEntity="App\Models\Auth\RefreshTokenDoctrineModel", mappedBy="user", cascade={"persist"})
     *
     * @var RefreshTokenModel[]|ArrayCollection
     */
    private $refreshTokens;

    /**
     * UserDoctrineModel constructor.
     *
     * @param string         $email
     * @param string         $password
     * @param array          $refreshTokens
     * @param \DateTime|null $createdAt
     * @param \DateTime|null $updatedAt
     * @param \DateTime|null $deletedAt
     */
    public function __construct(
        string $email,
        string $password,
        array $refreshTokens = [],
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null,
        \DateTime $deletedAt = null
    )
    {
        $this->email = $email;
        $this->password = Hash::make($password);
        $this->refreshTokens = new ArrayCollection($refreshTokens);
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email)
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
                    ? (array)$this->getCreatedAt()
                    : null,
                self::PROPERTY_UPDATED_AT => $this->getUpdatedAt()
                    ? (array)$this->getUpdatedAt()
                    : null,
                self::PROPERTY_DELETED_AT => $this->getDeletedAt()
                    ? (array)$this->getDeletedAt()
                    : null,
            ]
        );
    }
}
