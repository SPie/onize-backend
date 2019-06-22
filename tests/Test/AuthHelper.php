<?php

namespace Test;

use App\Models\User\RefreshTokenModel;
use App\Models\User\RefreshTokenModelFactory;
use App\Repositories\User\RefreshTokenRepository;
use Mockery;
use Mockery\MockInterface;
use SPie\LaravelJWT\Contracts\JWT;

/**
 * Trait AuthHelper
 *
 * @package Test
 */
trait AuthHelper
{

    /**
     * @return RefreshTokenModelFactory|MockInterface
     */
    protected function createRefreshTokenModelFactory(): RefreshTokenModelFactory
    {
        return Mockery::spy(RefreshTokenModelFactory::class);
    }

    /**
     * @return RefreshTokenModel|MockInterface
     */
    protected function createRefreshToken(): RefreshTokenModel
    {
        return Mockery::spy(RefreshTokenModel::class);
    }

    /**
     * @return RefreshTokenRepository|MockInterface
     */
    protected function createRefreshTokenRepository(): RefreshTokenRepository
    {
        return Mockery::spy(RefreshTokenRepository::class);
    }

    /**
     * @return JWT|MockInterface
     */
    protected function createJWT(): JWT
    {
        return Mockery::spy(JWT::class);
    }
}
