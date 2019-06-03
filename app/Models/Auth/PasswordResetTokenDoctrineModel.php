<?php

namespace App\Models\Auth;

use App\Models\AbstractDoctrineModel;
use App\Models\Timestamps;
use App\Models\User\UserModelInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PasswordResetTokenDoctrineModel
 *
 * @ORM\Table(name="password_reset_tokens")
 * @ORM\Entity(repositoryClass="App\Repositories\Auth\PasswordResetTokenDoctrineRepository")
 *
 * @package App\Models\Auth
 */
class PasswordResetTokenDoctrineModel extends AbstractDoctrineModel implements PasswordResetTokenModel
{

    use Timestamps;

    /**
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $token;

    /**
     * @ORM\Column(name="valid_until", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    private $validUntil;

    /**
     * @ORM\ManyToOne(targetEntity="App\Models\User\UserDoctrineModel", inversedBy="passwordResetTokens", cascade={"persist"})
     *
     * @var UserModelInterface
     */
    private $user;

    /**
     * PasswordResetTokenDoctrineModel constructor.
     *
     * @param string             $token
     * @param \DateTime          $validUntil
     * @param UserModelInterface $user
     * @param \DateTime|null     $createdAt
     * @param \DateTime|null     $updatedAt
     */
    public function __construct(
        string $token,
        \DateTime $validUntil,
        UserModelInterface $user,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null
    )
    {
        $this->token = $token;
        $this->validUntil = $validUntil;
        $this->user = $user;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param string $token
     *
     * @return PasswordResetTokenModel
     */
    public function setToken(string $token): PasswordResetTokenModel
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
     * @param \DateTime $validUntil
     *
     * @return PasswordResetTokenModel
     */
    public function setValidUntil(\DateTime $validUntil): PasswordResetTokenModel
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getValidUntil(): \DateTime
    {
        return $this->validUntil;
    }

    /**
     * @param UserModelInterface $user
     *
     * @return PasswordResetTokenModel
     */
    public function setUser(UserModelInterface $user): PasswordResetTokenModel
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
}