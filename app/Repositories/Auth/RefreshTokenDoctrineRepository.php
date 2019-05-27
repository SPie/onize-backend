<?php

namespace App\Repositories\Auth;

use App\Models\Auth\RefreshTokenModel;
use App\Models\ModelInterface;
use App\Repositories\AbstractDoctrineRepository;

/**
 * Class RefreshTokenDoctrineRepository
 *
 * @package App\Repositories\Auth
 */
class RefreshTokenDoctrineRepository extends AbstractDoctrineRepository implements RefreshTokenRepository
{

    /**
     * @param string $refreshTokenId
     *
     * @return RefreshTokenModel|ModelInterface|null
     */
    public function findOneByRefreshTokenId(string $refreshTokenId): ?RefreshTokenModel
    {
        return $this->findOneBy([RefreshTokenModel::PROPERTY_IDENTIFIER => $refreshTokenId]);
    }
}
