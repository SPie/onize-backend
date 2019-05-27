<?php

namespace App\Models\User;

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

    const PROPERTY_EMAIL          = 'email';
    const PROPERTY_PASSWORD       = 'password';
    const PROPERTY_REFRESH_TOKENS = 'refreshTokens';

    public function setEmail(string $email);

    public function getEmail(): string;

    public function setPassword(string $password);

    public function getPassword(): string;

    /**
     * @param RefreshTokenModel[] $refreshTokens
     *
     * @return UserModelInterface
     */
    public function setRefreshTokens(array $refreshTokens): UserModelInterface;

    public function addRefreshToken(RefreshTokenModel $refreshToken): UserModelInterface;

    /**
     * @return RefreshTokenModel[]|Collection
     */
    public function getRefreshTokens(): Collection;
}
