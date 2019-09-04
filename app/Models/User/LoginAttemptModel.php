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
    const PROPERTY_IP_ADDRESS   = 'ipAddress';
    const PROPERTY_IDENTIFIER   = 'identifier';
    const PROPERTY_ATTEMPTED_AT = 'attemptedAt';
    const PROPERTY_SUCCESS      = 'success';

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
     * @param \DateTimeImmutable $attemptedAt
     *
     * @return LoginAttemptModel
     */
    public function setAttemptedAt(\DateTimeImmutable $attemptedAt): LoginAttemptModel;

    /**
     * @return \DateTimeImmutable
     */
    public function getAttemptedAt(): \DateTimeImmutable;

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
