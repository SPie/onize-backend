<?php

namespace App\Models\User;

use App\Models\ModelInterface;
use App\Models\Timestampable;

/**
 * Interface RefreshTokenModel
 *
 * @package App\Models\User
 */
interface RefreshTokenModel extends ModelInterface, Timestampable
{

    const PROPERTY_IDENTIFIER  = 'identifier';
    const PROPERTY_VALID_UNTIL = 'validUntil';
    const PROPERTY_USER        = 'user';

    /**
     * @param string $identifier
     *
     * @return RefreshTokenModel
     */
    public function setIdentifier(string $identifier): RefreshTokenModel;

    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @param \DateTime|null $validUntil
     *
     * @return RefreshTokenModel
     */
    public function setValidUntil(?\DateTime $validUntil): RefreshTokenModel;

    /**
     * @return \DateTime|null
     */
    public function getValidUntil(): ?\DateTime;

    /**
     * @param UserModelInterface $user
     *
     * @return RefreshTokenModel
     */
    public function setUser(UserModelInterface $user): RefreshTokenModel;

    /**
     * @return UserModelInterface
     */
    public function getUser(): UserModelInterface;
}
