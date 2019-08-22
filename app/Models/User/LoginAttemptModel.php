<?php

namespace App\Models\User;

use App\Models\ModelInterface;

/**
 * Interface LoginAttemptModel
 *
 * @package App\Models\User
 */
interface LoginAttemptModel extends ModelInterface
{
    /**
     * @param string $ioAddress
     *
     * @return LoginAttemptModel
     */
    public function setIpAddress(string $ioAddress): LoginAttemptModel;

    /**
     * @return string
     */
    public function getIpAddress(): string;

    /**
     * @param string $identifier
     *
     * @return LoginAttemptModel
     */
    public function setIdentifier(string $identifier): LoginAttemptModel;

    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @param \DateTime $attemptedAt
     *
     * @return LoginAttemptModel
     */
    public function setAttemptedAt(\DateTime $attemptedAt): LoginAttemptModel;

    /**
     * @return \DateTime
     */
    public function getAttemptedAt(): \DateTime;

    /**
     * @param bool $success
     *
     * @return LoginAttemptModel
     */
    public function setSuccess(bool $success): LoginAttemptModel;

    /**
     * @return bool
     */
    public function wasSuccess(): bool;
}