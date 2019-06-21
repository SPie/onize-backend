<?php

namespace Test;

use App\Models\User\PasswordResetTokenDoctrineModel;
use App\Models\User\PasswordResetTokenModel;
use App\Models\User\PasswordResetTokenModelFactory;
use App\Models\User\RefreshTokenModel;
use App\Models\User\RefreshTokenModelFactory;
use App\Models\User\UserModelInterface;
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

    /**
     * @return PasswordResetTokenModel|MockInterface
     */
    protected function createPasswordResetTokenModel()
    {
        return Mockery::spy(PasswordResetTokenModel::class);
    }

    /**
     * @param string|null             $token
     * @param \DateTime|null          $validUntil
     * @param UserModelInterface|null $user
     *
     * @return PasswordResetTokenDoctrineModel
     */
    protected function createPasswordResetTokenDoctrineModel(
        string $token = null,
        \DateTime $validUntil = null,
        UserModelInterface $user = null
    ): PasswordResetTokenDoctrineModel
    {
        return new PasswordResetTokenDoctrineModel(
            $token ?: $this->getFaker()->uuid,
            $validUntil ?: $this->getFaker()->dateTime,
            $user ?: $this->createUserDoctrineModel()
        );
    }

    /**
     * @return PasswordResetTokenModelFactory|MockInterface
     */
    protected function createPasswordResetTokenModelFactory()
    {
        return Mockery::spy(PasswordResetTokenModelFactory::class);
    }
}
