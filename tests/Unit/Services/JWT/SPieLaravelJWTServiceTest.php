<?php

use App\Exceptions\Auth\InvalidAuthConfigurationException;
use App\Exceptions\Auth\NotAuthenticatedException;
use App\Models\User\UserModelInterface;
use App\Services\JWT\SPieLaravelJWTService;
use Illuminate\Auth\Access\AuthorizationException;
use Mockery\MockInterface;
use SPie\LaravelJWT\Contracts\JWTGuard;
use SPie\LaravelJWT\Exceptions\MissingRefreshTokenProviderException;
use SPie\LaravelJWT\Exceptions\MissingRefreshTokenRepositoryException;
use SPie\LaravelJWT\Exceptions\NotAuthenticatedException as JWTNotAuthenticatedException;
use SPie\LaravelJWT\JWT;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class SPieLaravelJWTServiceTest
 */
class SPieLaravelJWTServiceTest extends IntegrationTestCase
{

    //region Tests

    /**
     * @return void
     *
     * @throws AuthorizationException
     * @throws NotAuthenticatedException
     */
    public function testLoginWithoutRefreshToken(): void
    {
        $response = new Response();
        $response->headers->set($this->getFaker()->uuid, $this->getFaker()->uuid);
        $credentials = [
            $this->getFaker()->uuid => $this->getFaker()->uuid,
            $this->getFaker()->uuid => $this->getFaker()->uuid
        ];
        $jwtGuard = $this->createJWTGuard();
        $this->addReturnAccessToken($jwtGuard, $response);

        $this->assertEquals(
            $response,
            $this->createSPieLaravelJWTService($jwtGuard)->login(new Response(), $credentials)
        );

        $jwtGuard
            ->shouldHaveReceived('login')
            ->with($credentials)
            ->once();
    }

    /**
     * @return void
     *
     * @throws AuthorizationException
     * @throws NotAuthenticatedException
     */
    public function testLoginWithoutRefreshTokenWithoutUser(): void
    {
        $jwtGuard = $this->createJWTGuard();
        $this->addLogin($jwtGuard, new AuthorizationException());

        $this->expectException(AuthorizationException::class);

        $this->createSPieLaravelJWTService($jwtGuard)->login(
            new Response(),
            [
                $this->getFaker()->uuid => $this->getFaker()->uuid,
                $this->getFaker()->uuid => $this->getFaker()->uuid,
            ]
        );
    }

    /**
     * @return void
     */
    public function testLoginWithRefreshToken(): void
    {
        $refreshTokenKey = $this->getFaker()->uuid;
        $refreshToken = $this->getFaker()->uuid;
        $response = new Response();
        $response->headers->set($this->getFaker()->uuid, $this->getFaker()->uuid);
        $response->headers->set($refreshTokenKey, $refreshToken);
        $refreshTokenResponse = new Response();
        $refreshTokenResponse->headers->set($refreshTokenKey, $refreshToken);
        $jwtGuard = $this->createJWTGuard();
        $this
            ->addReturnAccessToken($jwtGuard, $response)
            ->addReturnRefreshToken($jwtGuard, $refreshTokenResponse);

        $spieLaravelJwtService = $this->createSPieLaravelJWTService($jwtGuard);

        $this->assertEquals(
            $response,
            $spieLaravelJwtService->login(
                new Response(),
                [
                    $this->getFaker()->uuid => $this->getFaker()->uuid,
                    $this->getFaker()->uuid => $this->getFaker()->uuid
                ],
                true
            )
        );

        $jwtGuard
            ->shouldHaveReceived('issueRefreshToken')
            ->once();
        $jwtGuard
            ->shouldHaveReceived('returnRefreshToken')
            ->once();
    }

    /**
     * @return void
     *
     * @throws AuthorizationException
     * @throws NotAuthenticatedException
     */
    public function testLoginWithRefreshTokenWithoutRefreshTokenRepository(): void
    {
        $jwtGuard = $this->createJWTGuard();
        $this->addIssueRefreshToken($jwtGuard, new MissingRefreshTokenRepositoryException());

        $this->expectException(InvalidAuthConfigurationException::class);

        $this->createSPieLaravelJWTService($jwtGuard)->login(
            new Response(),
            [
                $this->getFaker()->uuid => $this->getFaker()->uuid,
                $this->getFaker()->uuid => $this->getFaker()->uuid
            ],
            true
        );
    }

    /**
     * @return void
     *
     * @throws AuthorizationException
     * @throws NotAuthenticatedException
     */
    public function testLoginWithRefreshTokenWithoutRefreshTokenProvider(): void
    {
        $jwtGuard = $this->createJWTGuard();
        $this->addReturnRefreshToken($jwtGuard, new MissingRefreshTokenProviderException());

        $this->expectException(InvalidAuthConfigurationException::class);

        $this->createSPieLaravelJWTService($jwtGuard)->login(
            new Response(),
            [
                $this->getFaker()->uuid => $this->getFaker()->uuid,
                $this->getFaker()->uuid => $this->getFaker()->uuid
            ],
            true
        );
    }

    /**
     * @return void
     *
     * @throws AuthorizationException
     * @throws NotAuthenticatedException
     */
    public function testLoginWithRefreshTokenWithoutAuthenticatedUser(): void
    {
        $jwtGuard = $this->createJWTGuard();
        $this->addReturnRefreshToken($jwtGuard, new JWTNotAuthenticatedException());

        $this->expectException(NotAuthenticatedException::class);

        $this->createSPieLaravelJWTService($jwtGuard)->login(
            new Response(),
            [
                $this->getFaker()->uuid => $this->getFaker()->uuid,
                $this->getFaker()->uuid => $this->getFaker()->uuid
            ],
            true
        );
    }

    /**
     * @return void
     *
     * @throws AuthorizationException
     * @throws NotAuthenticatedException
     */
    public function testLoginWithRefreshTokenWithoutAuthenticatedUserOnIssueRefreshToken(): void
    {
        $jwtGuard = $this->createJWTGuard();
        $this->addIssueRefreshToken($jwtGuard, new JWTNotAuthenticatedException());

        $this->expectException(NotAuthenticatedException::class);

        $this->createSPieLaravelJWTService($jwtGuard)->login(
            new Response(),
            [
                $this->getFaker()->uuid => $this->getFaker()->uuid,
                $this->getFaker()->uuid => $this->getFaker()->uuid
            ],
            true
        );
    }

    /**
     * @return void
     */
    public function testLogout(): void
    {
        $response = new Response();
        $jwtGuard = $this->createJWTGuard();

        $this->assertEquals($response, $this->createSPieLaravelJWTService($jwtGuard)->logout($response));

        $jwtGuard
            ->shouldHaveReceived('logout')
            ->once();
    }

    /**
     * @return void
     */
    public function testRefreshAccessToken(): void
    {
        $emptyResponse = new Response();
        $response = new Response();
        $response->headers->set($this->getFaker()->uuid, $this->getFaker()->uuid);

        $jwtGuard = $this->createJWTGuard();
        $this
            ->addRefreshAccessToken($jwtGuard)
            ->addReturnAccessToken($jwtGuard, $response);

        $this->assertEquals(
            $response,
            $this->createSPieLaravelJWTService($jwtGuard)->refreshAccessToken($emptyResponse)
        );

        $jwtGuard
            ->shouldHaveReceived('refreshAccessToken')
            ->once();
        $jwtGuard
            ->shouldHaveReceived('returnAccessToken')
            ->with(
                Mockery::on(function ($argument) use ($emptyResponse) {
                    return $argument == $emptyResponse;
                })
            )
            ->once();
    }

    /**
     * @return void
     *
     * @throws NotAuthenticatedException
     */
    public function testRefreshAccessTokenNotAuthenticatedOnRefresh(): void
    {
        $jwtGuard = $this->createJWTGuard();
        $this->addRefreshAccessToken($jwtGuard, new JWTNotAuthenticatedException());

        $this->expectException(NotAuthenticatedException::class);

        $this->createSPieLaravelJWTService($jwtGuard)->refreshAccessToken(new Response());
    }

    /**
     * @return void
     *
     * @throws NotAuthenticatedException
     */
    public function testRefreshAccessTokenNotAuthenticatedOnSetResponse(): void
    {
        $jwtGuard = $this->createJWTGuard();
        $this
            ->addRefreshAccessToken($jwtGuard)
            ->addReturnAccessToken($jwtGuard, new JWTNotAuthenticatedException());

        $this->expectException(NotAuthenticatedException::class);

        $this->createSPieLaravelJWTService($jwtGuard)->refreshAccessToken(new Response());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testGetAuthenticatedUser(): void
    {
        $user = $this->createUser();

        $jwtGuard = $this->createJWTGuard();
        $this->addUser($jwtGuard, $user);

        $this->assertEquals($user, $this->createSPieLaravelJWTService($jwtGuard)->getAuthenticatedUser());
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testGetAuthenticatedUserWithoutUser(): void
    {
        $jwtGuard = $this->createJWTGuard();
        $this->addUser($jwtGuard);

        $this->expectException(NotAuthenticatedException::class);

        $this->createSPieLaravelJWTService($jwtGuard)->getAuthenticatedUser();
    }

    /**
     * @return void
     *
     * @throws JWTNotAuthenticatedException
     */
    public function testIssueTokens(): void
    {
        $user = $this->createUser();
        $response = new Response();
        $response->headers->set($this->getFaker()->uuid, $this->getFaker()->uuid);

        $jwtGuard = $this->createJWTGuard();
        $this
            ->addReturnAccessToken($jwtGuard, $response);

        $this->assertEquals(
            $response,
            $this->createSPieLaravelJWTService($jwtGuard)->issueTokens($user, new Response())
        );

        $jwtGuard
            ->shouldHaveReceived('issueAccessToken')
            ->with(
                Mockery::on(function ($argument) use ($user) {
                    return $argument == $user;
                })
            )
            ->once();
    }

    /**
     * @return void
     *
     * @throws JWTNotAuthenticatedException
     */
    public function testIssueTokensWithRefreshToken(): void
    {
        $refreshTokenKey = $this->getFaker()->uuid;
        $refreshToken = $this->getFaker()->uuid;

        $user = $this->createUser();
        $refreshTokenResponse = new Response();
        $refreshTokenResponse->headers->set($refreshTokenKey, $refreshToken);
        $accessTokenResponse = new Response();
        $accessTokenResponse->headers->set($this->getFaker()->uuid, $this->getFaker()->uuid);
        $accessTokenResponse->headers->set($refreshTokenKey, $refreshToken);

        $jwtGuard = $this->createJWTGuard();
        $this
            ->addReturnRefreshToken($jwtGuard, $refreshTokenResponse)
            ->addReturnAccessToken($jwtGuard, $accessTokenResponse);

        $this->assertEquals(
            $accessTokenResponse,
            $this->createSPieLaravelJWTService($jwtGuard)->issueTokens($user, new Response(), true)
        );

        $jwtGuard
            ->shouldHaveReceived('issueAccessToken')
            ->with(
                Mockery::on(function ($argument) use ($user) {
                    return $argument == $user;
                })
            )
            ->once();
        $jwtGuard
            ->shouldHaveReceived('issueRefreshToken')
            ->once();
        $jwtGuard
            ->shouldHaveReceived('returnRefreshToken')
            ->with(
                Mockery::on(function ($argument) {
                    return $argument == new Response();
                })
            )
            ->once();
        $jwtGuard
            ->shouldHaveReceived('returnAccessToken')
            ->with(
                Mockery::on(function ($argument) use ($refreshTokenResponse) {
                    return $argument == $refreshTokenResponse;
                })
            )
            ->once();
    }

    //endregion

    //region Mocks

    /**
     * @param JWTGuard|null $jwtGuard
     *
     * @return SPieLaravelJWTService|MockInterface
     */
    private function createSPieLaravelJWTService(JWTGuard $jwtGuard = null): SPieLaravelJWTService
    {
        $spieLaravelJwtService = Mockery::spy(
            SPieLaravelJWTService::class,
            [
                $jwtGuard ?: $this->createJWTGuard(),
            ]
        );
        $spieLaravelJwtService->makePartial();

        return $spieLaravelJwtService;
    }

    /**
     * @return JWTGuard|MockInterface
     */
    private function createJWTGuard(): JWTGuard
    {
        return Mockery::spy(JWTGuard::class);
    }

    /**
     * @param JWTGuard|MockInterface $jwtGuard
     * @param Response|\Exception    $response
     *
     * @return SPieLaravelJWTServiceTest
     */
    private function addReturnAccessToken(JWTGuard $jwtGuard, $response): SPieLaravelJWTServiceTest
    {
        $expectation = $jwtGuard->shouldReceive('returnAccessToken');

        if ($response instanceof \Exception) {
            $expectation->andThrow($response);

            return $this;
        }

        $expectation->andReturn($response);

        return $this;
    }

    /**
     * @param JWTGuard|MockInterface $jwtGuard
     * @param JWT|\Exception         $refreshToken
     *
     * @return SPieLaravelJWTServiceTest
     */
    private function addIssueRefreshToken(JWTGuard $jwtGuard, $refreshToken): SPieLaravelJWTServiceTest
    {
        $expectation = $jwtGuard->shouldReceive('issueRefreshToken');

        if ($refreshToken instanceof \Exception) {
            $expectation->andThrow($refreshToken);

            return $this;
        }

        $expectation->andReturn($refreshToken);

        return $this;
    }

    /**
     * @param JWTGuard|MockInterface $jwtGuard
     * @param Response|\Exception    $response
     *
     * @return SPieLaravelJWTServiceTest
     */
    private function addReturnRefreshToken(JWTGuard $jwtGuard, $response): SPieLaravelJWTServiceTest
    {
        $expectation = $jwtGuard->shouldReceive('returnRefreshToken');

        if ($response instanceof \Exception) {
            $expectation->andThrow($response);

            return $this;
        }

        $expectation->andReturn($response);

        return $this;
    }

    /**
     * @param JWTGuard|MockInterface $jwtGuard
     * @param \Exception|null        $exception
     *
     * @return SPieLaravelJWTServiceTest
     */
    private function addLogin(JWTGuard $jwtGuard, $exception = null): SPieLaravelJWTServiceTest
    {
        $expectation = $jwtGuard->shouldReceive('login');

        if ($exception instanceof \Exception) {
            $expectation->andThrow($exception);

            return $this;
        }

        $expectation->andReturn($jwtGuard);

        return $this;
    }

    /**
     * @param JWTGuard|MockInterface $jwtGuard
     * @param \Exception|null        $exception
     *
     * @return SPieLaravelJWTServiceTest
     */
    private function addRefreshAccessToken(JWTGuard $jwtGuard, $exception = null): SPieLaravelJWTServiceTest
    {
        $expectation = $jwtGuard->shouldReceive('refreshAccessToken');

        if ($exception instanceof \Exception) {
            $expectation->andThrow($exception);
        }

        return $this;
    }

    /**
     * @param JWTGuard|MockInterface             $jwtGuard
     * @param UserModelInterface|\Exception|null $user
     *
     * @return SPieLaravelJWTServiceTest
     */
    private function addUser(JWTGuard $jwtGuard, $user = null): SPieLaravelJWTServiceTest
    {
        $expectation = $jwtGuard->shouldReceive('user');

        if ($user instanceof \Exception) {
            $expectation->andThrow($user);

            return $this;
        }

        $expectation->andReturn($user);

        return $this;
    }

    /**
     * @return UserModelInterface|MockInterface
     */
    private function createUser(): UserModelInterface
    {
        return Mockery::mock(UserModelInterface::class);
    }

    //endregion
}
