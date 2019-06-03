<?php

namespace App\Models\User;

use App\Models\Auth\PasswordResetTokenModel;
use App\Models\Auth\RefreshTokenModel;
use App\Models\ModelInterface;
use App\Models\SoftDeletable;
use App\Models\Timestampable;
use Illuminate\Support\Collection;
use SPie\LaravelJWT\Contracts\JWTAuthenticatable;

/**
 * Interface UserModelInterface
 *
 * @package App\Models\User
 */
interface UserModelInterface extends ModelInterface, Timestampable, SoftDeletable, JWTAuthenticatable
{

    const PROPERTY_EMAIL                 = 'email';
    const PROPERTY_PASSWORD              = 'password';
    const PROPERTY_REFRESH_TOKENS        = 'refreshTokens';
    const PROPERTY_PASSWORD_RESET_TOKENS = 'passwordResetTokens';

    /**
     * @param string $email
     *
     * @return mixed
     */
    public function setEmail(string $email);

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @param string $password
     *
     * @return mixed
     */
    public function setPassword(string $password);

    /**
     * @return string
     */
    public function getPassword(): string;

    /**
     * @param RefreshTokenModel[] $refreshTokens
     *
     * @return UserModelInterface
     */
    public function setRefreshTokens(array $refreshTokens): UserModelInterface;

    /**
     * @param RefreshTokenModel $refreshToken
     *
     * @return UserModelInterface
     */
    public function addRefreshToken(RefreshTokenModel $refreshToken): UserModelInterface;

    /**
     * @return RefreshTokenModel[]|Collection
     */
    public function getRefreshTokens(): Collection;

    /**
     * @param array $passwordResetTokens
     *
     * @return UserModelInterface
     */
    public function setPasswordResetTokens(array $passwordResetTokens): UserModelInterface;

    /**
     * @param PasswordResetTokenModel $passwordResetToken
     *
     * @return UserModelInterface
     */
    public function addPasswordResetToken(PasswordResetTokenModel $passwordResetToken): UserModelInterface;

    /**
     * @return PasswordResetTokenModel[]|Collection
     */
    public function getPasswordResetTokens(): Collection;
}
