<?php

namespace App\Models\Auth;

use App\Models\AbstractDoctrineModel;
use App\Models\Timestamps;
use App\Models\User\UserModelInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class RefreshTokenDoctrineModel
 *
 * @ORM\Table(name="refresh_tokens")
 * @ORM\Entity(repositoryClass="App\Repositories\Auth\RefreshTokenDoctrineRepository")
 *
 * @package App\Models\Auth
 */
final class RefreshTokenDoctrineModel extends AbstractDoctrineModel implements RefreshTokenModel
{

    use Timestamps;

    /**
     * @ORM\Column(name="identifier", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $identifier;

    /**
     * @ORM\ManyToOne(targetEntity="App\Models\User\UserDoctrineModel", inversedBy="refreshTokens", cascade={"persist"})
     *
     * @var UserModelInterface
     */
    private $user;

    /**
     * @ORM\Column(name="valid_until", type="datetime", nullable=true)
     *
     * @var \DateTime|null
     */
    private $validUntil;

    /**
     * RefreshTokenDoctrineModel constructor.
     *
     * @param string             $identifier
     * @param UserModelInterface $user
     * @param \DateTime|null     $validUntil
     * @param \DateTime|null     $createdAt
     * @param \DateTime|null     $updatedAt
     */
    public function __construct(
        string $identifier,
        UserModelInterface $user,
        \DateTime $validUntil = null,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null
    )
    {
        $this->identifier = $identifier;
        $this->validUntil = $validUntil;
        $this->user = $user;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param string $identifier
     *
     * @return RefreshTokenModel
     */
    public function setIdentifier(string $identifier): RefreshTokenModel
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
     * @param UserModelInterface $user
     *
     * @return RefreshTokenModel
     */
    public function setUser(UserModelInterface $user): RefreshTokenModel
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
     * @param \DateTime|null $validUntil
     *
     * @return RefreshTokenModel
     */
    public function setValidUntil(?\DateTime $validUntil): RefreshTokenModel
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getValidUntil(): ?\DateTime
    {
        return $this->validUntil;
    }
}
