<?php

namespace App\Services\JWT;

use App\Exceptions\Auth\InvalidAuthConfigurationException;
use App\Exceptions\Auth\NotAuthenticatedException;
use App\Models\User\UserModelInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use SPie\LaravelJWT\Contracts\JWTGuard;
use SPie\LaravelJWT\Exceptions\JWTException;
use SPie\LaravelJWT\Exceptions\NotAuthenticatedException as JWTNotAuthenticatedException;
use SPie\LaravelJWT\Contracts\JWTHandler;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SPieLaravelJWTService
 *
 * @package App\Services\JWT
 */
class SPieLaravelJWTService implements JWTService
{

    /**
     * @var JWTGuard
     */
    private $jwtGuard;

    /**
     * @var JWTHandler
     */
    private $jwtHandler;

    /**
     * SPieLaravelJWTService constructor.
     *
     * @param JWTGuard|Guard $jwtGuard
     * @param JWTHandler     $jwtHandler
     */
    public function __construct(Guard $jwtGuard, JWTHandler $jwtHandler)
    {
        $this->jwtGuard = $jwtGuard;
        $this->jwtHandler = $jwtHandler;
    }

    /**
     * @return JWTGuard
     */
    protected function getJwtGuard(): JWTGuard
    {
        return $this->jwtGuard;
    }

    /**
     * @return JWTHandler
     */
    public function getJwtHandler(): JWTHandler
    {
        return $this->jwtHandler;
    }

    /**
     * @param Response $response
     * @param array    $credentials
     * @param bool     $withRefreshToken
     *
     * @return Response
     *
     * @throws NotAuthenticatedException
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function login(Response $response, array $credentials, bool $withRefreshToken = false): Response
    {
        $this->getJwtGuard()->login($credentials);

        if ($withRefreshToken) {
            try {
                $this->getJwtGuard()->issueRefreshToken();
                $response = $this->getJwtGuard()->returnRefreshToken($response);
            } catch (JWTNotAuthenticatedException $e) {
                throw new NotAuthenticatedException();
            } catch (JWTException $e) {
                throw new InvalidAuthConfigurationException();
            }
        }

        return $this->getJwtGuard()->returnAccessToken($response);
    }

    /**
     * @param Response $response
     *
     * @return Response
     */
    public function logout(Response $response): Response
    {
        $this->getJwtGuard()->logout();

        return $response;
    }

    /**
     * @param Response $response
     *
     * @return Response
     *
     * @throws NotAuthenticatedException
     * @throws \Exception
     */
    public function refreshAccessToken(Response $response): Response
    {
        try {
            $this->getJwtGuard()->refreshAccessToken();

            return $this->getJwtGuard()->returnAccessToken($response);
        } catch (JWTNotAuthenticatedException $e) {
            throw new NotAuthenticatedException();
        }
    }

    /**
     * @return UserModelInterface|Authenticatable
     *
     * @throws \Exception
     */
    public function getAuthenticatedUser(): UserModelInterface
    {
        $user = $this->getJwtGuard()->user();
        if (!$user) {
            throw new NotAuthenticatedException();
        }

        return $user;
    }

    /**
     * @param UserModelInterface $user
     * @param Response           $response
     * @param bool               $withRefreshToken
     *
     * @return Response
     *
     * @throws JWTNotAuthenticatedException
     * @throws \Exception
     */
    public function issueTokens(UserModelInterface $user, Response $response, bool $withRefreshToken = false): Response
    {
        $this->getJwtGuard()->issueAccessToken($user);

        if ($withRefreshToken) {
            $this->getJwtGuard()->issueRefreshToken();

            $response = $this->getJwtGuard()->returnRefreshToken($response);
        }

        return $this->getJwtGuard()->returnAccessToken($response);
    }

    /**
     * @param UserModelInterface $user
     * @param int|null           $ttl
     *
     * @return string
     */
    public function createJWT(UserModelInterface $user, int $ttl = null): string
    {
        return $this->getJwtHandler()->createJWT($user->getAuthIdentifier(), $user->getCustomClaims(), $ttl)
            ->getJWT();
    }
}
