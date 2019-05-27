<?php

namespace App\Repositories\Auth;

use App\Models\Auth\RefreshTokenModel;
use App\Repositories\RepositoryInterface;

/**
 * Interface RefreshTokenRepository
 *
 * @package App\Repositories\Auth
 */
interface RefreshTokenRepository extends RepositoryInterface
{

    /**
     * @param string $refreshTokenId
     *
     * @return RefreshTokenModel|null
     */
    public function findOneByRefreshTokenId(string $refreshTokenId): ?RefreshTokenModel;
}
