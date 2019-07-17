<?php

use App\Exceptions\Auth\InvalidAuthConfigurationException;
use App\Exceptions\Auth\NotAuthenticatedException;
use App\Models\User\UserModelInterface;
use App\Services\JWT\SPieLaravelJWTService;
use Illuminate\Auth\Access\AuthorizationException;
use Mockery\MockInterface;
use SPie\LaravelJWT\Contracts\JWTGuard;
use SPie\LaravelJWT\Exceptions\InvalidTokenException;
use SPie\LaravelJWT\Exceptions\JWTException;
use SPie\LaravelJWT\Exceptions\MissingRefreshTokenProviderException;
use SPie\LaravelJWT\Exceptions\MissingRefreshTokenRepositoryException;
use SPie\LaravelJWT\Exceptions\NotAuthenticatedException as JWTNotAuthenticatedException;
use SPie\LaravelJWT\Contracts\JWT;
use SPie\LaravelJWT\Contracts\JWTHandler;
use Symfony\Component\HttpFoundation\Response;
use Test\UserHelper;

/**
 * Class SPieLaravelJWTServiceTest
 */
class SPieLaravelJWTServiceTest extends TestCase
{
    use UserHelper;

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
        $user = $this->createUserModel();

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
        $user = $this->createUserModel();
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

        $user = $this->createUserModel();
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

    /**
     * @return void
     */
    public function testCreateJWT(): void
    {
        $authIdentifier = $this->getFaker()->uuid;
        $customClaims = [$this->getFaker()->uuid];
        $user = $this->createUserModel();
        $this
            ->mockUserModelGetAuthIdentifier($user, $authIdentifier)
            ->mockUserModelGetCustomClaims($user, $customClaims);
        $ttl = $this->getFaker()->numberBetween();
        $token = $this->getFaker()->uuid;
        $jwt = $this->createJWT();
        $this->mockJWTGetJWT($jwt, $token);
        $jwtHandler = $this->createJWTHandler();
        $this->mockJWTHandlerCreateJWT($jwtHandler, $jwt, $authIdentifier, $customClaims, $ttl);

        $this->assertEquals(
            $token,
            $this->createSPieLaravelJWTService(null, $jwtHandler)->createJWT($user, $ttl)
        );
    }

    /**
     * @return void
     */
    public function testVerifyJWT(): void
    {
        $email = $this->getFaker()->safeEmail;
        $token = $this->getFaker()->uuid;
        $jwt = $this->createJWT();
        $this->mockJWTGetSubject($jwt, $email);
        $jwtHandler = $this->createJWTHandler();
        $this->mockJWTHandlerGetValidJWT($jwtHandler, $jwt, $token);

        $this->assertEquals(
            $email,
            $this->createSPieLaravelJWTService(null, $jwtHandler)->verifyJWT($token)
        );
    }

    /**
     * @return void
     */
    public function testVerifyJWTWithInvalidJWT(): void
    {
        $token = $this->getFaker()->uuid;
        $jwtHandler = $this->createJWTHandler();
        $this->mockJWTHandlerGetValidJWT($jwtHandler, new InvalidTokenException(), $token);

        $this->expectException(NotAuthenticatedException::class);

        $this->createSPieLaravelJWTService(null, $jwtHandler)->verifyJWT($token);
    }

    //endregion

    //region Mocks

    /**
     * @param JWTGuard|null   $jwtGuard
     * @param JWTHandler|null $jwtHandler
     *
     * @return SPieLaravelJWTService|MockInterface
     */
    private function createSPieLaravelJWTService(
        JWTGuard $jwtGuard = null,
        JWTHandler $jwtHandler = null
    ): SPieLaravelJWTService {
        $spieLaravelJwtService = Mockery::spy(
            SPieLaravelJWTService::class,
            [
                $jwtGuard ?: $this->createJWTGuard(),
                $jwtHandler ?: $this->createJWTHandler(),
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
     * @return JWTHandler
     */
    private function createJWTHandler(): JWTHandler
    {
        return Mockery::spy(JWTHandler::class);
    }

    /**
     * @param JWTHandler|MockInterface $jwtHandler
     * @param JWT                      $jwt
     * @param string                   $subject
     * @param array                    $payload
     * @param int|null                 $ttl
     *
     * @return SPieLaravelJWTServiceTest
     */
    private function mockJWTHandlerCreateJWT(
        MockInterface $jwtHandler,
        JWT $jwt,
        string $subject,
        array $payload,
        int $ttl = null
    ): SPieLaravelJWTServiceTest {
        $arguments = [
            $subject,
            $payload,
        ];
        if ($ttl !== null) {
            $arguments[] = $ttl;
        }

        $jwtHandler
            ->shouldReceive('createJWT')
            ->withArgs($arguments)
            ->andReturn($jwt);

        return $this;
    }

    /**
     * @param JWTHandler|MockInterface $jwtHandler
     * @param JWT|\Exception           $jwt
     * @param string                   $token
     *
     * @return SPieLaravelJWTServiceTest
     */
    private function mockJWTHandlerGetValidJWT(MockInterface $jwtHandler, $jwt, string $token): SPieLaravelJWTServiceTest
    {
        $jwtHandler
            ->shouldReceive('getValidJWT')
            ->with($token)
            ->andThrow($jwt);

        return $this;
    }

    /**
     * @return JWT
     */
    private function createJWT(): JWT
    {
        return Mockery::spy(JWT::class);
    }

    /**
     * @param JWT|MockInterface $jwt
     * @param string            $token
     *
     * @return SPieLaravelJWTServiceTest
     */
    private function mockJWTGetJWT(MockInterface $jwt, string $token): SPieLaravelJWTServiceTest
    {
        $jwt
            ->shouldReceive('getJWT')
            ->andReturn($token);

        return $this;
    }

    /**
     * @param JWT|MockInterface $jwt
     * @param string            $subject
     *
     * @return SPieLaravelJWTServiceTest
     */
    private function mockJWTGetSubject(MockInterface $jwt, string $subject): SPieLaravelJWTServiceTest
    {
        $jwt
            ->shouldReceive('getSubject')
            ->andReturn($subject);

        return $this;
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

    //endregion
}
