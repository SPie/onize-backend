<?php

use App\Exceptions\Auth\NotAuthenticatedException;
use App\Exceptions\InvalidParameterException;
use App\Http\Controllers\User\UsersController;
use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelInterface;
use App\Services\JWT\JWTService;
use App\Services\User\UsersServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;
use Test\AuthHelper;
use Test\ControllerHelper;
use Test\RequestResponseHelper;
use Test\UserHelper;

/**
 * Class UsersControllerTest
 */
class UsersControllerTest extends IntegrationTestCase
{

    use AuthHelper;
    use ControllerHelper;
    use RequestResponseHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testGetEmailValidators(): void
    {
        $usersController = $this->createUsersController();

        $this->assertEquals(
            [
                'email' => [
                    'email',
                    'required',
                    Rule::unique(UserDoctrineModel::class, 'email')
                ]
            ],
            $this->getReflectionMethod($usersController, 'getEmailValidators')->invoke($usersController)
        );
    }

    /**
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testGetPasswordValidators(): void
    {
        $usersController = $this->createUsersController();

        $this->assertEquals(
            [
                'password'        => [
                    'min:8',
                    'required'
                ],
                'passwordConfirm' => [
                    'required',
                    'same:password'
                ],
            ],
            $this->getReflectionMethod($usersController, 'getPasswordValidators')->invoke($usersController)
        );
    }

    /**
     * @return void
     *
     * @throws \ReflectionException
     */
    public function testGetPasswordValidatorsWithCurrentPassword(): void
    {
        $currentPassword = $this->getFaker()->password;
        $usersController = $this->createUsersController();

        $this->assertEquals(
            [
                'password'        => [
                    'min:8',
                    'required'
                ],
                'passwordConfirm' => [
                    'required',
                    'same:password'
                ],
                'currentPassword' => [
                    'required',
                    function () {}
                ],
            ],
            $this->getReflectionMethod($usersController, 'getPasswordValidators')
                 ->invokeArgs($usersController, [$currentPassword])
        );
    }

    /**
     * @return void
     */
    public function testRegisterWithInvalidData(): void
    {
        $request = $this->createRequest();
        $emailValidators = [$this->getFaker()->uuid => $this->getFaker()->uuid];
        $passwordValidators = [$this->getFaker()->uuid => $this->getFaker()->uuid];
        $usersController = $this->createUsersController();
        $this
            ->mockUsersControllerGetEmailValidators($usersController, $emailValidators)
            ->mockUsersControllerGetPasswordValidators($usersController, $passwordValidators)
            ->mockControllerValidate(
                $usersController,
                $this->createValidationException(),
                $request,
                \array_merge($emailValidators, $passwordValidators)
            );

        $this->expectException(ValidationException::class);

        $usersController->register($request, $this->createJWTService());
    }

    /**
     * @return void
     */
    public function testRegisterWithInvalidParameters(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('remember', $this->getFaker()->boolean);
        $emailValidators = [$this->getFaker()->uuid => $this->getFaker()->uuid];
        $passwordValidators = [$this->getFaker()->uuid => $this->getFaker()->uuid];
        $userData = [$this->getFaker()->uuid];
        $usersService = $this->createUsersService();
        $this->mockUsersServiceCreateUser($usersService, new InvalidParameterException(), $userData);
        $usersController = $this->createUsersController($usersService);
        $this
            ->mockUsersControllerGetEmailValidators($usersController, $emailValidators)
            ->mockUsersControllerGetPasswordValidators($usersController, $passwordValidators)
            ->mockControllerValidate(
                $usersController,
                $userData,
                $request,
                \array_merge($emailValidators, $passwordValidators)
            );

        $this->expectException(InvalidParameterException::class);

        $usersController->register($request, $this->createJWTService());
    }

    /**
     * @return void
     */
    public function testRegister(): void
    {
        $withRefreshToken = $this->getFaker()->boolean;
        $request = $this->createRequest();
        $request->offsetSet('remember', $withRefreshToken);
        $response = $this->createJsonResponse();
        $emailValidators = [$this->getFaker()->uuid => $this->getFaker()->uuid];
        $passwordValidators = [$this->getFaker()->uuid => $this->getFaker()->uuid];
        $userData = [$this->getFaker()->uuid];
        $user = $this->createUserModel();
        $usersService = $this->createUsersService();
        $this->mockUsersServiceCreateUser($usersService, $user, $userData);
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceIssueTokens($jwtService, $response, $user, $response, $withRefreshToken);
        $usersController = $this->createUsersController($usersService);
        $this
            ->mockUsersControllerGetEmailValidators($usersController, $emailValidators)
            ->mockUsersControllerGetPasswordValidators($usersController, $passwordValidators)
            ->mockControllerValidate(
                $usersController,
                $userData,
                $request,
                \array_merge($emailValidators, $passwordValidators)
            )
            ->mockControllerCreateResponse($usersController, $response, ['user' => $user], 201);

        $this->assertEquals($response, $usersController->register($request, $jwtService));
    }

    /**
     * @return void
     */
    public function testChangePasswordWithInvalidData(): void
    {
        $request = $this->createRequest();
        $currentPassword = $this->getFaker()->password;
        $passwordValidators = [$this->getFaker()->uuid => $this->getFaker()->uuid];
        $user = $this->createUserModel();
        $this->mockUserModelGetAuthPassword($user, $currentPassword);
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceGetAuthenticatedUser($jwtService, $user);
        $usersController = $this->createUsersController();
        $this
            ->mockUsersControllerGetPasswordValidators($usersController, $passwordValidators, $currentPassword)
            ->mockControllerValidate($usersController, $this->createValidationException(), $request, $passwordValidators);

        $this->expectException(ValidationException::class);

        $usersController->changePassword($request, $jwtService);
    }

    /**
     * @return void
     */
    public function testChangePasswordWithInvalidParameters(): void
    {
        $request = $this->createRequest();
        $currentPassword = $this->getFaker()->password;
        $passwordValidators = [$this->getFaker()->uuid => $this->getFaker()->uuid];
        $userData = [$this->getFaker()->uuid];
        $user = $this->createUserModel();
        $this->mockUserModelGetAuthPassword($user, $currentPassword);
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceGetAuthenticatedUser($jwtService, $user);
        $usersService = $this->createUsersService();
        $this->mockUsersServiceEditUser($usersService, new InvalidParameterException(), $user, $userData);
        $usersController = $this->createUsersController($usersService);
        $this
            ->mockUsersControllerGetPasswordValidators($usersController, $passwordValidators, $currentPassword)
            ->mockControllerValidate($usersController, $userData, $request, $passwordValidators);

        $this->expectException(InvalidParameterException::class);

        $usersController->changePassword($request, $jwtService);
    }

    /**
     * @return void
     */
    public function testChangePassword(): void
    {
        $request = $this->createRequest();
        $response = $this->createJsonResponse();
        $currentPassword = $this->getFaker()->password;
        $passwordValidators = [$this->getFaker()->uuid => $this->getFaker()->uuid];
        $userData = [$this->getFaker()->uuid];
        $user = $this->createUserModel();
        $this->mockUserModelGetAuthPassword($user, $currentPassword);
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceGetAuthenticatedUser($jwtService, $user);
        $usersService = $this->createUsersService();
        $this->mockUsersServiceEditUser($usersService, $user, $user, $userData);
        $usersController = $this->createUsersController($usersService);
        $this
            ->mockUsersControllerGetPasswordValidators($usersController, $passwordValidators, $currentPassword)
            ->mockControllerValidate($usersController, $userData, $request, $passwordValidators)
            ->mockControllerCreateResponse($usersController, $response, ['user' => $user]);

        $this->assertEquals($response, $usersController->changePassword($request, $jwtService));
    }

    /**
     * @return void
     */
    public function testLoginWithInvalidRequestParameters(): void
    {
        $request = $this->createRequest();
        $usersController = $this->createUsersController();
        $this->mockControllerValidate(
            $usersController,
            $this->createValidationException(),
            $request,
            [
                'email'    => ['required'],
                'password' => ['required'],
            ]
        );

        $this->expectException(ValidationException::class);

        $usersController->login($request, $this->createJWTService());
    }

    /**
     * @return void
     */
    public function testLoginWithoutRefreshToken(): void
    {
        $request = $this->createRequest();
        $response = $this->createJsonResponse('', 204);
        $email = $this->getFaker()->safeEmail;
        $password = $this->getFaker()->password;
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceLogin(
            $jwtService,
            $response,
            $response,
            [
                'email'    => $email,
                'password' => $password,
            ],
            false
        );
        $usersController = $this->createUsersController();
        $this
            ->mockControllerCreateResponse($usersController, $response, [], 204)
            ->mockControllerValidate(
                $usersController,
                [
                    'email'    => $email,
                    'password' => $password,
                ],
                $request,
                [
                    'email'    => ['required'],
                    'password' => ['required'],
                ]
            );

        $this->assertEquals($response, $usersController->login($request, $jwtService));
    }

    /**
     * @return void
     */
    public function testLoginWithRefreshToken(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('remember', true);
        $response = $this->createJsonResponse('', 204);
        $email = $this->getFaker()->safeEmail;
        $password = $this->getFaker()->password;
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceLogin(
            $jwtService,
            $response,
            $response,
            [
                'email'    => $email,
                'password' => $password,
            ],
            true
        );
        $usersController = $this->createUsersController();
        $this
            ->mockControllerCreateResponse($usersController, $response, [], 204)
            ->mockControllerValidate(
                $usersController,
                [
                    'email'    => $email,
                    'password' => $password,
                ],
                $request,
                [
                    'email'    => ['required'],
                    'password' => ['required'],
                ]
            );

        $this->assertEquals($response, $usersController->login($request, $jwtService));
    }

    /**
     * @return void
     */
    public function testLogout(): void
    {
        $response = $this->createJsonResponse('', 204);
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceLogout($jwtService, $response);
        $usersController = $this->createUsersController();
        $this->mockControllerCreateResponse($usersController, $response, [], 204);

        $this->assertEquals($response, $usersController->logout($jwtService));
    }

    /**
     * @return void
     */
    public function testAuthenticatedUser(): void
    {
        $user = $this->createUser();
        $response = $this->createJsonResponse();
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceGetAuthenticatedUser($jwtService, $user);
        $usersController = $this->createUsersController();
        $this->mockControllerCreateResponse($usersController, $response, ['user' => $user]);

        $this->assertEquals($response, $usersController->authenticatedUser($jwtService));
    }

    /**
     * @return void
     */
    public function testAuthenticatedUserWithoutUser(): void
    {
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceGetAuthenticatedUser($jwtService, new NotAuthenticatedException());

        $this->expectException(NotAuthenticatedException::class);

        $this->createUsersController()->authenticatedUser($jwtService);
    }

    /**
     * @return void
     */
    public function testRefreshAccessTokenWithoutAuthenticatedUser(): void
    {
        $response = $this->createJsonResponse();
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceRefreshAccessToken($jwtService, new NotAuthenticatedException(), $response);
        $usersController = $this->createUsersController();
        $this->mockControllerCreateResponse($usersController, $response, [], 204);

        $this->expectException(NotAuthenticatedException::class);

        $usersController->refreshAccessToken($jwtService);
    }

    /**
     * @return void
     */
    public function testRefreshAccessToken(): void
    {
        $response = $this->createJsonResponse();
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceRefreshAccessToken($jwtService, $response, $response);
        $usersController = $this->createUsersController();
        $this->mockControllerCreateResponse($usersController, $response, [], 204);

        $this->assertEquals($response, $usersController->refreshAccessToken($jwtService));
    }

    //endregion

    //region Mocks

    /**
     * @param UsersServiceInterface|null $usersService
     * @param JWTService|null            $jwtService
     *
     * @return UsersController|MockInterface
     */
    private function createUsersController(
        UsersServiceInterface $usersService = null,
        JWTService $jwtService = null
    ): UsersController
    {
        $usersController = Mockery::spy(
            UsersController::class,
            [
                $usersService ?: $this->createUserService(),
                $jwtService ?: $this->createJWTService()
            ]
        );
        $usersController
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        return $usersController;
    }

    /**
     * @return UsersServiceInterface|MockInterface
     */
    private function createUserService(): UsersServiceInterface
    {
        return Mockery::spy(UsersServiceInterface::class);
    }

    /**
     * @return UserModelInterface|MockInterface
     */
    private function createUser(): UserModelInterface
    {
        return Mockery::spy(UserModelInterface::class);
    }

    /**
     * @return JWTService|MockInterface
     */
    private function createJWTService(): JWTService
    {
        return Mockery::spy(JWTService::class);
    }

    /**
     * @param MockInterface $jwtService
     * @param Response      $response
     * @param Response      $inputResponse
     * @param array         $credentials
     * @param bool          $withRefreshToken
     *
     * @return UsersControllerTest
     */
    private function mockJWTServiceLogin(
        MockInterface $jwtService,
        Response $response,
        Response $inputResponse,
        array $credentials,
        bool $withRefreshToken
    ): UsersControllerTest
    {
        $jwtService
            ->shouldReceive('login')
            ->with(
                Mockery::on(function ($argument) use ($inputResponse) {
                    return $argument == $inputResponse;
                }),
                $credentials,
                $withRefreshToken
            )
            ->andReturn($response);

        return $this;
    }

    /**
     * @param UsersController|MockInterface $usersController
     * @param array                         $emailValidators
     *
     * @return UsersControllerTest
     */
    private function mockUsersControllerGetEmailValidators(
        MockInterface $usersController,
        array $emailValidators
    ): UsersControllerTest
    {
        $usersController
            ->shouldReceive('getEmailValidators')
            ->andReturn($emailValidators);

        return $this;
    }

    /**
     * @param UsersController|MockInterface $usersController
     * @param array                         $passwordValidators
     * @param string|null                   $currentPassword
     *
     * @return UsersControllerTest
     */
    private function mockUsersControllerGetPasswordValidators(
        MockInterface $usersController,
        array $passwordValidators,
        string $currentPassword = null
    ): UsersControllerTest
    {
        $expectation = $usersController
            ->shouldReceive('getPasswordValidators')
            ->andReturn($passwordValidators);

        if ($currentPassword !== null) {
            $expectation->with($currentPassword);
        }

        return $this;
    }

    /**
     * @param UsersController $usersController
     * @param string          $methodName
     *
     * @return \ReflectionMethod
     *
     * @throws \ReflectionException
     */
    private function getReflectionMethod(UsersController $usersController, string $methodName): \ReflectionMethod
    {
        $reflectionObject = new \ReflectionObject($usersController);
        $reflectionMethod = $reflectionObject->getMethod($methodName);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod;
    }

    //endregion
}
