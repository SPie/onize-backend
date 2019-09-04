<?php

namespace App\Models\User;

use App\Models\AbstractDoctrineModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class LoginAttemptDoctrineModel
 *
 * @ORM\Table(name="login_attempts")
 * @ORM\Entity(repositoryClass="App\Repositories\User\LoginAttemptDoctrineRepository")
 *
 * @package App\Models\User
 */
final class LoginAttemptDoctrineModel extends AbstractDoctrineModel implements LoginAttemptModel
{
    /**
     * @ORM\Column(name="ip_address", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $ipAddress;

    /**
     * @ORM\Column(name="identifier", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $identifier;

    /**
     * @ORM\Column(name="attempted_at", type="datetime", nullable=true)
     *
     * @var \DateTimeImmutable
     */
    private $attemptedAt;

    /**
     * @ORM\Column(name="success", type="boolean", nullable=true)
     *
     * @var bool
     */
    private $success;

    /**
     * LoginAttemptDoctrineModel constructor.
     *
     * @param string             $ipAddress
     * @param string             $identifier
     * @param \DateTimeImmutable $attemptedAt
     * @param bool               $success
     */
    public function __construct(string $ipAddress, string $identifier, \DateTimeImmutable $attemptedAt, bool $success)
    {
        $this->ipAddress = $ipAddress;
        $this->identifier = $identifier;
        $this->attemptedAt = $attemptedAt;
        $this->success = $success;
    }

    /**
     * @param string $ioAddress
     *
     * @return LoginAttemptModel
     */
    public function setIpAddress(string $ioAddress): LoginAttemptModel
    {
        $this->ipAddress = $ioAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $identifier
     *
     * @return LoginAttemptModel
     */
    public function setIdentifier(string $identifier): LoginAttemptModel
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
     * @param \DateTimeImmutable $attemptedAt
     *
     * @return LoginAttemptModel
     */
    public function setAttemptedAt(\DateTimeImmutable $attemptedAt): LoginAttemptModel
    {
        $this->attemptedAt = $attemptedAt;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getAttemptedAt(): \DateTimeImmutable
    {
        return $this->attemptedAt;
    }

    /**
     * @param bool $success
     *
     * @return LoginAttemptModel
     */
    public function setSuccess(bool $success): LoginAttemptModel
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @return bool
     */
    public function wasSuccess(): bool
    {
        return $this->success;
    }
}
