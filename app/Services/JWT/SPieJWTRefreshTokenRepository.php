<?php

namespace App\Services\JWT;

use App\Exceptions\ModelNotFoundException;
use App\Models\Auth\RefreshTokenModel;
use App\Models\Auth\RefreshTokenModelFactory;
use App\Models\User\UserModelInterface;
use App\Repositories\Auth\RefreshTokenRepository;
use App\Repositories\User\UserRepositoryInterface;
use SPie\LaravelJWT\Contracts\JWT;
use SPie\LaravelJWT\Contracts\RefreshTokenRepository as SPieRefreshTokenRepository;

/**
 * Class SPieJWTRefreshTokenRepository
 *
 * @package App\Services\JWT
 */
class SPieJWTRefreshTokenRepository implements JWTRefreshTokenRepository
{

    /**
     * @var RefreshTokenModelFactory
     */
    private $refreshTokenModelFactory;

    /**
     * @var RefreshTokenRepository
     */
    private $refreshTokenRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * SPieJWTRefreshTokenRepository constructor.
     *
     * @param RefreshTokenModelFactory $refreshTokenModelFactory
     * @param RefreshTokenRepository   $refreshTokenRepository
     * @param UserRepositoryInterface  $userRepository
     */
    public function __construct(
        RefreshTokenModelFactory $refreshTokenModelFactory,
        RefreshTokenRepository $refreshTokenRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->refreshTokenModelFactory = $refreshTokenModelFactory;
        $this->refreshTokenRepository = $refreshTokenRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @return RefreshTokenModelFactory
     */
    protected function getRefreshTokenModelFactory(): RefreshTokenModelFactory
    {
        return $this->refreshTokenModelFactory;
    }

    /**
     * @return RefreshTokenRepository
     */
    protected function getRefreshTokenRepository(): RefreshTokenRepository
    {
        return $this->refreshTokenRepository;
    }

    /**
     * @return UserRepositoryInterface
     */
    protected function getUserRepository(): UserRepositoryInterface
    {
        return $this->userRepository;
    }

    /**
     * @param JWT $refreshToken
     *
     * @return SPieRefreshTokenRepository
     *
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function storeRefreshToken(JWT $refreshToken): SPieRefreshTokenRepository
    {
        $user = $this->getUserRepository()->findOneByEmail($refreshToken->getSubject());
        if (!$user) {
            throw new ModelNotFoundException(UserModelInterface::class, $refreshToken->getSubject());
        }

        $this->getRefreshTokenRepository()->save(
            $this->getRefreshTokenModelFactory()->create([
                RefreshTokenModel::PROPERTY_IDENTIFIER  => $refreshToken->getRefreshTokenId(),
                RefreshTokenModel::PROPERTY_VALID_UNTIL => $refreshToken->getExpiresAt()
                    ? new \DateTime($refreshToken->getExpiresAt()->format('Y-m-d H:i:s'))
                    : null,
                RefreshTokenModel::PROPERTY_USER        => $user,
            ])
        );

        return $this;
    }

    /**
     * @param string $refreshTokenId
     *
     * @return SPieRefreshTokenRepository
     *
     * @throws ModelNotFoundException
     * @throws \Exception
     */
    public function revokeRefreshToken(string $refreshTokenId): SPieRefreshTokenRepository
    {
        $refreshToken = $this->getRefreshTokenRepository()->findOneByRefreshTokenId($refreshTokenId);
        if (!$refreshToken) {
            throw new ModelNotFoundException(RefreshTokenModel::class, $refreshTokenId);
        }

        $this->getRefreshTokenRepository()->save($refreshToken->setValidUntil(new \DateTime()));

        return $this;
    }

    /**
     * @param string $refreshTokenId
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function isRefreshTokenRevoked(string $refreshTokenId): bool
    {
        $refreshToken = $this->getRefreshTokenRepository()->findOneByRefreshTokenId($refreshTokenId);
        if (!$refreshToken) {
            return true;
        }

        return !empty($refreshToken->getValidUntil())
            ? $refreshToken->getValidUntil() < new \DateTime()
            : false;
    }
}
