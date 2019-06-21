<?php

namespace App\Repositories\User;

use App\Models\User\RefreshTokenModel;
use App\Repositories\RepositoryInterface;

/**
 * Interface RefreshTokenRepository
 *
 * @package App\Repositories\User
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
