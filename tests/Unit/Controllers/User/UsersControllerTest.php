<?php

use App\Exceptions\Auth\NotAuthenticatedException;
use App\Http\Controllers\User\UsersController;
use App\Http\Response\JsonResponseData;
use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelInterface;
use App\Services\JWT\JWTService;
use App\Services\User\UsersServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Response;
use Test\AuthHelper;

/**
 * Class UsersControllerTest
 */
class UsersControllerTest extends IntegrationTestCase
{

    use AuthHelper;

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
     *
     * @throws ValidationException
     */
    public function testRegister(): void
    {
        $input = [
            'email'           => $this->getFaker()->safeEmail,
            'password'        => $this->getFaker()->password,
            'passwordConfirm' => $this->getFaker()->password,
        ];

        $user = $this->createUser();

        $response = new JsonResponse(
            new JsonResponseData([
                'user' => $user,
            ]),
            201
        );

        $userService = $this->createUserService();
        $this->addCreateUser($userService, $user);

        $jwtService = $this->createJWTService();
        $this->addIssueTokens($jwtService, $response);

        $usersController = $this->createUsersController($userService, $jwtService);
        $this->addValidate($usersController, $input);

        $this->assertEquals(
            $response,
            $usersController->register(new Request())
        );

        $usersController
            ->shouldHaveReceived('validate')
            ->with(
                Mockery::on(function ($argument) {
                    return $argument == new Request();
                }),
                [
                    'email'           => [
                        'email',
                        'required',
                        Rule::unique(UserDoctrineModel::class, 'email'),
                    ],
                    'password'        => [
                        'min:8',
                        'required',
                    ],
                    'passwordConfirm' => [
                        'required',
                        'same:password',
                    ],
                ]
            )
            ->once();

        $userService
            ->shouldHaveReceived('createUser')
            ->with($input)
            ->once();

        $jwtService
            ->shouldHaveReceived('issueTokens')
            ->with(
                Mockery::on(function ($argument) use ($user) {
                    return $argument == $user;
                }),
                Mockery::on(function ($argument) use ($response) {
                    return $argument == $response;
                }),
                false
            )
            ->once();
    }

    /**
     * @return void
     *
     * @throws ValidationException
     */
    public function testRegisterWithRefreshToken(): void
    {
        $user = $this->createUser();

        $request = new Request();
        $request->offsetSet('stayLoggedIn', true);

        $response = new JsonResponse(
            new JsonResponseData([
                'user' => $user,
            ]),
            201
        );

        $userService = $this->createUserService();
        $this->addCreateUser($userService, $user);

        $jwtService = $this->createJWTService();
        $this->addIssueTokens($jwtService, $response);

        $usersController = $this->createUsersController($userService, $jwtService);
        $this->addValidate($usersController, []);

        $this->assertEquals(
            $response,
            $usersController->register($request)
        );

        $jwtService
            ->shouldHaveReceived('issueTokens')
            ->with(
                Mockery::on(function ($argument) use ($user) {
                    return $argument == $user;
                }),
                Mockery::on(function ($argument) use ($response) {
                    return $argument == $response;
                }),
                true
            )
            ->once();
    }

    /**
     * @return void
     *
     * @throws ValidationException
     */
    public function testChangePassword(): void
    {
        $password = $this->getFaker()->password;
        $user = $this->createUser();
        $user
            ->shouldReceive('getAuthPassword')
            ->andReturn($password);
        $request = new Request();

        $input = [
            'password'        => $this->getFaker()->password,
            'passwordConfirm' => $this->getFaker()->password,
            'currentPassword' => $password,
        ];

        $userService = $this->createUserService();
        $this->addEditUser($userService, $this->createUser());

        $jwtService = $this->createJWTService();
        $this->addGetAuthenticatedUser($jwtService, $user);

        $usersController = $this->createUsersController($userService, $jwtService);
        $this->addValidate($usersController, $input);

        $this->assertInstanceOf(JsonResponse::class, $usersController->changePassword($request));

        $usersController
            ->shouldHaveReceived('validate')
            ->with(
                Mockery::on(function ($argument) {
                    return $argument == new Request();
                }),
                Mockery::on(function ($argument) {
                    return (
                        isset($argument['password'])
                        && isset($argument['passwordConfirm'])
                        && isset($argument['currentPassword'])
                    );
                })
            )
            ->once();

        $userService
            ->shouldHaveReceived('editUser')
            ->with(
                Mockery::on(function ($argument) use ($user) {
                    return $argument == $user;
                }),
                $input
            )
            ->once();
    }

    /**
     * @return void
     *
     * @throws ValidationException
     */
    public function testChangePasswordWithoutAuthenticatedUser(): void
    {
        $jwtService = $this->createJWTService();
        $this->addGetAuthenticatedUser($jwtService, new NotAuthenticatedException());

        $this->expectException(NotAuthenticatedException::class);

        $this->createUsersController($this->createUserService(), $jwtService)->changePassword(new Request());
    }

//    /**
//     * @return void
//     */
//    public function testLogin(): void
//    {
//        $response = new JsonResponse([], 204);
//        $response->headers->set($this->getFaker()->uuid, $this->getFaker()->uuid);
//        $credentials = [
//            'email'    => $this->getFaker()->safeEmail,
//            'password' => $this->getFaker()->password,
//        ];
//
//        $jwtService = $this->createJWTService();
//        $this->mockJWTserviceLogin(
//            $jwtService,
//            $response,
//            new JsonResponse([], 204),
//            $credentials,
//            false
//        );
//
//        $usersController = $this->createUsersController();
//        $this->mockUsersControllerValidate(
//            $usersController,
//            $credentials,
//            new Request(),
//            [
//                'email'    => ['required'],
//                'password' => ['required'],
//            ]
//        );
//
//        $this->assertEquals($response, $usersController->login(new Request()));
//    }
//
//    /**
//     * @return void
//     */
//    public function testLoginWithInvalidCredentialsFromRequest(): void
//    {
//        $jwtService = $this->createJWTService();
//
//        $authController = $this->createAuthController($jwtService);
//        $this->addValidate($authController, Mockery::mock(ValidationException::class));
//
//        $this->expectException(ValidationException::class);
//
//        $authController->login(new Request());
//    }
//
//    /**
//     * @return void
//     */
//    public function testLoginWithRefreshToken(): void
//    {
//        $request = new Request();
//        $request->offsetSet('remember', true);
//        $response = new JsonResponse([], 204);
//        $response->headers->set($this->getFaker()->uuid, $this->getFaker()->uuid);
//        $credentials = [
//            'email'    => $this->getFaker()->safeEmail,
//            'password' => $this->getFaker()->password,
//        ];
//
//        $jwtService = $this->createJWTService();
//        $this->addLogin($jwtService, $response);
//
//        $authController = $this->createAuthController($jwtService);
//        $this->addValidate($authController, $credentials);
//
//        $this->assertEquals($response, $authController->login($request));
//
//        $jwtService
//            ->shouldHaveReceived('login')
//            ->with(
//                Mockery::on(function ($argument) {
//                    return (
//                        ($argument instanceof JsonResponse)
//                        && $argument->getStatusCode() == 204
//                        && empty($argument->getData())
//                    );
//                }),
//                $credentials,
//                true
//            )
//            ->once();
//    }

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
     * @param MockInterface    $usersController
     * @param array|\Exception $inputArray
     * @param Request          $request
     * @param array            $rules
     *
     * @return UsersControllerTest
     */
    private function mockUsersControllerValidate(
        MockInterface $usersController,
        $inputArray,
        Request $request,
        array $rules
    ): UsersControllerTest
    {
        $usersController
            ->shouldReceive('validate')
            ->with(
                Mockery::on(function ($argument) use ($request) {

                }),
                $rules
            )
            ->andThrow($inputArray);

        return $this;
    }

    /**
     * @param UsersController|MockInterface $usersController
     * @param array|\Exception              $input
     *
     * @return UsersControllerTest
     */
    private function addValidate(UsersController $usersController, $input): UsersControllerTest
    {
        $expectation = $usersController->shouldReceive('validate');

        if ($input instanceof \Exception) {
            $expectation->andThrow($input);

            return $this;
        }

        $expectation->andReturn($input);

        return $this;
    }

    /**
     * @return UsersServiceInterface|MockInterface
     */
    private function createUserService(): UsersServiceInterface
    {
        return Mockery::spy(UsersServiceInterface::class);
    }

    /**
     * @param UsersServiceInterface|MockInterface $usersService
     * @param UserModelInterface|\Exception       $user
     *
     * @return UsersControllerTest
     */
    private function addCreateUser(UsersServiceInterface $usersService, $user): UsersControllerTest
    {
        $expectation = $usersService->shouldReceive('createUser');

        if ($user instanceof \Exception) {
            $expectation->andThrow($user);

            return $this;
        }

        $expectation->andReturn($user);

        return $this;
    }

    /**
     * @param UsersServiceInterface|MockInterface $usersService
     * @param UserModelInterface|\Exception       $user
     *
     * @return UsersControllerTest
     */
    private function addEditUser(UsersServiceInterface $usersService, $user): UsersControllerTest
    {
        $expectation = $usersService->shouldReceive('editUser');

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
     * @param JWTService|MockInterface $jwtService
     * @param Response|\Exception      $response
     *
     * @return UsersControllerTest
     */
    private function addIssueTokens(JWTService $jwtService, $response): UsersControllerTest
    {
        $expectation = $jwtService->shouldReceive('issueTokens');

        if ($response instanceof \Exception) {
            $expectation->andThrow($response);

            return $this;
        }

        $expectation->andReturn($response);

        return $this;
    }

    /**
     * @param JWTService|MockInterface      $jwtService
     * @param UserModelInterface|\Exception $user
     *
     * @return UsersControllerTest
     */
    private function addGetAuthenticatedUser(JWTService $jwtService, $user): UsersControllerTest
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
     * @param MockInterface $jwtService
     * @param Response      $response
     * @param Response      $inputResponse
     * @param array         $credentials
     * @param bool          $withRefreshToken
     *
     * @return UsersControllerTest
     */
    private function mockJWTserviceLogin(
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
