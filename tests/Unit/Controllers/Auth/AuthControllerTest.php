<?php

use App\Exceptions\Auth\NotAuthenticatedException;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Response\JsonResponseData;
use App\Models\User\UserModelInterface;
use App\Services\JWT\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthControllerTest
 */
class AuthControllerTest extends IntegrationTestCase
{

    //region Tests

    /**
     * @return void
     *
     * @throws ValidationException
     */
    public function testLogin(): void
    {
        $response = new JsonResponse([], 204);
        $response->headers->set($this->getFaker()->uuid, $this->getFaker()->uuid);
        $credentials = [
            'email'    => $this->getFaker()->safeEmail,
            'password' => $this->getFaker()->password,
        ];

        $jwtService = $this->createJWTService();
        $this->addLogin($jwtService, $response);

        $authController = $this->createAuthController($jwtService);
        $this->addValidate($authController, $credentials);

        $this->assertEquals($response, $authController->login(new Request()));

        $authController
            ->shouldHaveReceived('validate')
            ->with(
                Mockery::on(function ($argument) {
                    return $argument == new Request();
                }),
                [
                    'email' => [
                        'required',
                    ],
                    'password' => [
                        'required',
                    ],
                ]
            )
            ->once();

        $jwtService
            ->shouldHaveReceived('login')
            ->with(
                Mockery::on(function ($argument) {
                    return (
                        ($argument instanceof JsonResponse)
                        && $argument->getStatusCode() == 204
                        && empty($argument->getData())
                    );
                }),
                $credentials,
                false
            )
            ->once();
    }

    /**
     * @return void
     *
     * @throws ValidationException
     */
    public function testLoginWithInvalidCredentialsFromRequest(): void
    {
        $jwtService = $this->createJWTService();

        $authController = $this->createAuthController($jwtService);
        $this->addValidate($authController, Mockery::mock(ValidationException::class));

        $this->expectException(ValidationException::class);

        $authController->login(new Request());
    }

    /**
     * @return void
     *
     * @throws ValidationException
     */
    public function testLoginWithRefreshToken(): void
    {
        $request = new Request();
        $request->offsetSet('remember', true);
        $response = new JsonResponse([], 204);
        $response->headers->set($this->getFaker()->uuid, $this->getFaker()->uuid);
        $credentials = [
            'email'    => $this->getFaker()->safeEmail,
            'password' => $this->getFaker()->password,
        ];

        $jwtService = $this->createJWTService();
        $this->addLogin($jwtService, $response);

        $authController = $this->createAuthController($jwtService);
        $this->addValidate($authController, $credentials);

        $this->assertEquals($response, $authController->login($request));

        $jwtService
            ->shouldHaveReceived('login')
            ->with(
                Mockery::on(function ($argument) {
                    return (
                        ($argument instanceof JsonResponse)
                        && $argument->getStatusCode() == 204
                        && empty($argument->getData())
                    );
                }),
                $credentials,
                true
            )
            ->once();
    }

    /**
     * @return void
     */
    public function testLogout(): void
    {
        $response = new JsonResponse([], 204);

        $jwtService = $this->createJWTService();
        $this->addLogout($jwtService, $response);

        $this->assertEquals($response, $this->createAuthController($jwtService)->logout());

        $jwtService
            ->shouldHaveReceived('logout')
            ->with(
                Mockery::on(function ($argument) {
                    return (
                        ($argument instanceof JsonResponse)
                        && $argument->getStatusCode() == 204
                        && $argument->getData() == []
                    );
                })
            )
            ->once();
    }

    /**
     * @return void
     */
    public function testAuthenticatedUser(): void
    {
        $user = $this->createUser();

        $jwtService = $this->createJWTService();
        $this->addGetAuthenticatedUser($jwtService, $user);

        $this->assertEquals(
            new JsonResponse(
                new JsonResponseData(
                    [
                        'user' => $user,
                    ]
                )
            ),
            $this->createAuthController($jwtService)->authenticatedUser()
        );
    }

    /**
     * @return void
     */
    public function testAuthenticatedUserWithoutUser(): void
    {
        $jwtService = $this->createJWTService();
        $this->addGetAuthenticatedUser($jwtService, new NotAuthenticatedException());

        $this->expectException(NotAuthenticatedException::class);

        $this->createAuthController($jwtService)->authenticatedUser();
    }

    /**
     * @return void
     */
    public function testRefreshAccessToken(): void
    {
        $response = new JsonResponse([], 204);
        $response->headers->set($this->getFaker()->uuid, $this->getFaker()->uuid);

        $jwtService = $this->createJWTService();
        $this->addRefreshAccessToken($jwtService, $response);

        $this->assertEquals($response, $this->createAuthController($jwtService)->refreshAccessToken());

        $jwtService
            ->shouldHaveReceived('refreshAccessToken')
            ->with(
                Mockery::on(function ($argument) {
                    return (
                        ($argument instanceof JsonResponse)
                        && $argument->getStatusCode() == 204
                        && $argument->getData() == []
                    );
                })
            )
            ->once();
    }

    /**
     * @return void
     */
    public function testRefreshAccessTokenWithoutAuthenticatedUser(): void
    {
        $jwtService = $this->createJWTService();
        $this->addRefreshAccessToken($jwtService, new NotAuthenticatedException());

        $this->expectException(NotAuthenticatedException::class);

        $this->createAuthController($jwtService)->refreshAccessToken();
    }

    //endregion

    //region Mocks

    /**
     * @param JWTService $jwtService
     *
     * @return AuthController|MockInterface
     */
    private function createAuthController(JWTService $jwtService): AuthController
    {
        $authController = Mockery::spy(
            AuthController::class,
            [
                $jwtService
            ]
        );
        $authController
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        return $authController;
    }

    /**
     * @param AuthController|MockInterface $authController
     * @param array|\Exception             $credentials
     *
     * @return AuthControllerTest
     */
    private function addValidate(AuthController $authController, $credentials): AuthControllerTest
    {
        $expected = $authController->shouldReceive('validate');

        if ($credentials instanceof \Exception) {
            $expected->andThrow($credentials);

            return $this;
        }

        $expected->andReturn($credentials);

        return $this;
    }

    /**
     * @return JWTService|MockInterface
     */
    private function createJWTService(): JWTService
    {
        return Mockery::spy(JWTService::class);
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param Response                 $response
     *
     * @return AuthControllerTest
     */
    private function addLogin(JWTService $jwtService, Response $response): AuthControllerTest
    {
        $jwtService
            ->shouldReceive('login')
            ->andReturn($response);

        return $this;
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param Response                 $response
     *
     * @return AuthControllerTest
     */
    private function addLogout(JWTService $jwtService, Response $response): AuthControllerTest
    {
        $jwtService
            ->shouldReceive('logout')
            ->andReturn($response);

        return $this;
    }

    /**
     * @param JWTService|MockInterface           $jwtService
     * @param UserModelInterface|\Exception|null $user
     *
     * @return AuthControllerTest
     */
    private function addGetAuthenticatedUser(JWTService $jwtService, $user = null): AuthControllerTest
    {
        $expectation = $jwtService->shouldReceive('getAuthenticatedUser');

        if ($user instanceof \Exception) {
            $expectation->andThrow($user);

            return $this;
        }

        $expectation->andReturn($user);

        return $this;
    }

    /**
     * @param JWTService|MockInterface $jwtService
     * @param Response|\Exception      $response
     *
     * @return AuthControllerTest
     */
    private function addRefreshAccessToken(JWTService $jwtService, $response): AuthControllerTest
    {
       $expectation = $jwtService->shouldReceive('refreshAccessToken');

       if ($response instanceof \Exception) {
           $expectation->andThrow($response);

           return $this;
       }

       $expectation->andReturn($response);

        return $this;
    }

    /**
     * @return UserModelInterface|MockInterface
     */
    private function createUser(): UserModelInterface
    {
        return Mockery::spy(UserModelInterface::class);
    }

    //endregion
}
