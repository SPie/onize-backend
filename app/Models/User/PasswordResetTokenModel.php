<?php

namespace App\Models\User;

use App\Models\ModelInterface;
use App\Models\Timestampable;

/**
 * Interface PasswordResetTokenModel
 *
 * @package App\Models\User
 */
interface PasswordResetTokenModel extends ModelInterface, Timestampable
{

    const PROPERTY_TOKEN = 'token';
    const PROPERTY_VALID_UNTIL = 'validUntil';
    const PROPERTY_USER = 'user';

    /**
     * @param string $token
     *
     * @return PasswordResetTokenModel
     */
    public function setToken(string $token): PasswordResetTokenModel;

    /**
     * @return string
     */
    public function getToken(): string;

    /**
     * @param \DateTime $validUntil
     *
     * @return PasswordResetTokenModel
     */
    public function setValidUntil(\DateTime $validUntil): PasswordResetTokenModel;

    /**
     * @return \DateTime
     */
    public function getValidUntil(): \DateTime;

    /**
     * @param UserModelInterface $user
     *
     * @return PasswordResetTokenModel
     */
    public function setUser(UserModelInterface $user): PasswordResetTokenModel;

    /**
     * @return UserModelInterface
     */
    public function getUser(): UserModelInterface;
}