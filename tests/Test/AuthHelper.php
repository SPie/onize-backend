<?php

namespace Test;

use App\Models\User\RefreshTokenModel;
use App\Models\User\RefreshTokenModelFactory;
use App\Models\User\UserModelInterface;
use App\Repositories\User\RefreshTokenRepository;
use App\Services\JWT\JWTService;
use Mockery;
use Mockery\MockInterface;
use SPie\LaravelJWT\Contracts\JWT;
use Symfony\Component\HttpFoundation\Response;

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
    protected function getRefreshTokenRepository(): RefreshTokenRepository
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
     * @return JWTService
     */
    private function createJWTService(): JWTService
    {
        return Mockery::spy(JWTService::class);
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param Response|\Exception      $response
     * @param Response                 $inputResponse
     * @param array                    $credentials
     * @param bool|null                $withRefreshToken
     *
     * @return $this
     */
    protected function mockJWTServiceLogin(
        MockInterface $jwtService,
        $response,
        Response $inputResponse,
        array $credentials,
        bool $withRefreshToken = null
    ) {
        $arguments = [
            Mockery::on(function ($argument) use ($inputResponse) {
                return $argument == $inputResponse;
            }),
            $credentials
        ];
        if ($withRefreshToken !== null) {
            $arguments[] = $withRefreshToken;
        }

        $jwtService
            ->shouldReceive('login')
            ->withArgs($arguments)
            ->andThrow($response);

        return $this;
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param Response                 $response
     *
     * @return $this
     */
    protected function mockJWTServiceLogout(MockInterface $jwtService, Response $response)
    {
        $jwtService
            ->shouldReceive('logout')
            ->with(Mockery::on(function ($argument) use ($response) {
                return $argument == $response;
            }))
            ->andReturn($response);

        return $this;
    }

    /**
     * @param JWTService|MockInterface      $jwtService
     * @param UserModelInterface|\Exception $user
     *
     * @return $this
     */
    protected function mockJWTServiceGetAuthenticatedUser(MockInterface $jwtService, $user)
    {
        $jwtService
            ->shouldReceive('getAuthenticatedUser')
            ->andThrow($user);

        return $this;
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param Response|\Exception      $response
     * @param UserModelInterface       $user
     * @param Response                 $inputResponse
     * @param bool                     $withRefreshToken
     *
     * @return $this
     */
    protected function mockJWTServiceIssueTokens(
        MockInterface $jwtService,
        $response,
        UserModelInterface $user,
        Response $inputResponse,
        bool $withRefreshToken
    ) {
        $jwtService
            ->shouldReceive('issueTokens')
            ->with(
                Mockery::on(function ($argument) use ($user) {
                    return $argument == $user;
                }),
                Mockery::on(function ($argument) use ($inputResponse) {
                    return $argument == $inputResponse;
                }),
                $withRefreshToken
            )
            ->andReturn($response);

        return $this;
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param Response|\Exception      $response
     * @param Response                 $inputResponse
     *
     * @return $this
     */
    protected function mockJWTServiceRefreshAccessToken(MockInterface $jwtService, $response, Response $inputResponse)
    {
        $jwtService
            ->shouldReceive('refreshAccessToken')
            ->with(Mockery::on(function ($argument) use ($inputResponse) {
                return $argument == $inputResponse;
            }))
            ->andThrow($response);

        return $this;
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param string                   $jwt
     * @param UserModelInterface       $user
     * @param int|null                 $ttl
     *
     * @return $this
     */
    protected function mockJWTServiceCreateJWT(
        MockInterface $jwtService,
        string $jwt,
        UserModelInterface $user,
        int $ttl = null
    ) {
        $arguments = [$user];
        if ($ttl !== null) {
            $arguments[] = $ttl;
        }
        $jwtService
            ->shouldReceive('createJWT')
            ->withArgs($arguments)
            ->andReturn($jwt);

        return $this;
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param string|\Exception        $subject
     * @param string                   $token
     *
     * @return $this
     */
    protected function mockJWTServiceVerifyJWT(MockInterface $jwtService, $subject, string $token)
    {
        $expectation = $jwtService
            ->shouldReceive('verifyJWT')
            ->with($token);

        if ($subject instanceof \Exception) {
            $expectation->andThrow($subject);

            return $this;
        }

        $expectation->andReturn($subject);

        return $this;
    }
}
