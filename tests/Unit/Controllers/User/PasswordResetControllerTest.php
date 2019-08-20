<?php

use App\Exceptions\Auth\NotAuthenticatedException;
use App\Exceptions\ModelNotFoundException;
use App\Http\Controllers\User\PasswordResetController;
use App\Models\User\UserModelInterface;
use Illuminate\Validation\ValidationException;
use Test\EmailHelper;
use Test\ReflectionMethodHelper;
use Test\RequestResponseHelper;
use Test\UserHelper;

/**
 * Class PasswordResetControllerTest
 */
final class PasswordResetControllerTest extends TestCase
{

    use EmailHelper;
    use RequestResponseHelper;
    use ReflectionMethodHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testStart(): void
    {
        $token = $this->getFaker()->uuid;
        $email = $this->getFaker()->safeEmail;
        $tokenPlaceholder = $this->getFaker()->uuid;
        $finishUrl = $this->getFaker()->url;
        $request = $this->createRequest();
        $request->offsetSet('email', $email);
        $request->offsetSet('finishUrl', $finishUrl . $tokenPlaceholder);
        $user = $this->createUserModel();
        $usersService = $this->createUsersService();
        $this->mockUsersServiceGetUserByEmail($usersService, $user, $email);
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceCreateJWT($jwtService, $token, $user, 15);
        $emailService = $this->createEmailService();

        $this->assertJsonResponse(
            $this->createJsonResponse($this->createJsonResponseData(), 204),
            $this->createPasswordResetController($tokenPlaceholder)
                ->start($request, $usersService, $jwtService, $emailService)
        );
        $this->assertEmailServicePasswordResetEmail($emailService, $email, $finishUrl . $token);
    }

    /**
     * @return void
     */
    public function testStartWithoutEmail(): void
    {
        $this->expectException(ValidationException::class);

        $this->createPasswordResetController()->start(
            $this->createRequest(),
            $this->createUsersService(),
            $this->createJWTService(),
            $this->createEmailService()
        );
    }

    /**
     * @return void
     */
    public function testStartWithInvalidEmail(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('email', $this->getFaker()->uuid);

        $this->expectException(ValidationException::class);

        $this->createPasswordResetController()->start(
            $request,
            $this->createUsersService(),
            $this->createJWTService(),
            $this->createEmailService()
        );
    }
    /**
     * @return void
     */
    public function testStartWithoutUser(): void
    {
        $email = $this->getFaker()->safeEmail;
        $request = $this->createRequest();
        $request->offsetSet('email', $email);
        $request->offsetSet('finishUrl', $this->getFaker()->url);
        $usersService = $this->createUsersService();
        $this->mockUsersServiceGetUserByEmail(
            $usersService,
            new ModelNotFoundException(UserModelInterface::class),
            $email
        );
        $emailService = $this->createEmailService();

        $this->assertJsonResponse(
            $this->createJsonResponse($this->createJsonResponseData(), 204),
            $this->createPasswordResetController()->start(
                $request,
                $usersService,
                $this->createJWTService(),
                $emailService
            )
        );
        $emailService->shouldNotHaveReceived('queueEmail');
    }

    /**
     * @return void
     */
    public function testStartWithoutFinishUrl(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('email', $this->getFaker()->email);

        $this->expectException(ValidationException::class);

        $this->createPasswordResetController()->start(
            $request,
            $this->createUsersService(),
            $this->createJWTService(),
            $this->createEmailService()
        );
    }

    /**
     * @return void
     */
    public function testGetEmailFromRequest(): void
    {
        $email = $this->getFaker()->safeEmail;
        $request = $this->createRequest();
        $request->offsetSet('email', $email);

        $this->assertEquals(
            $email,
            $this->runReflectionMethod(
                $this->createPasswordResetController(),
                'getEmailFromRequest',
                [$request]
            )
        );
    }

    /**
     * @return void
     */
    public function testGetEmailFromRequestWithoutEmail(): void
    {
        $this->expectException(ValidationException::class);

        $this->runReflectionMethod(
            $this->createPasswordResetController(),
            'getEmailFromRequest',
            [$this->createRequest()]
        );
    }

    /**
     * @return void
     */
    public function testGetEmailFromRequestWithInvalidEmail(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('email', $this->getFaker()->uuid);

        $this->expectException(ValidationException::class);

        $this->runReflectionMethod(
            $this->createPasswordResetController(),
            'getEmailFromRequest',
            [$request]
        );
    }

    public function testVerifyTokenWithSuccess(): void
    {
        $token = $this->getFaker()->uuid;
        $email = $this->getFaker()->safeEmail;
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $token);
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceVerifyJWT($jwtService, $email, $token);
        $usersService = $this->createUsersService();
        $this->mockUsersServiceGetUserByEmail($usersService, $this->createUserModel(), $email);

        $this->assertJsonResponse(
            $this->createJsonResponse($this->createJsonResponseData(), 204),
            $this->createPasswordResetController()->verifyToken($request, $usersService, $jwtService)
        );
    }

    /**
     * @return void
     */
    public function testVerifyTokenWithoutToken(): void
    {
        $this->expectException(ValidationException::class);

        $this->createPasswordResetController()->verifyToken(
            $this->createRequest(),
            $this->createUsersService(),
            $this->createJWTService()
        );
    }

    /**
     * @return void
     */
    public function testVerifyTokenWithInvalidToken(): void
    {
        $token = $this->getFaker()->uuid;
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $token);
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceVerifyJWT($jwtService, new NotAuthenticatedException(), $token);

        $this->expectException(NotAuthenticatedException::class);

        $this->createPasswordResetController()->verifyToken($request, $this->createUsersService(), $jwtService);
    }

    /**
     * @return void
     */
    public function testVerifyTokenWithInvalidUser(): void
    {
        $token = $this->getFaker()->uuid;
        $email = $this->getFaker()->safeEmail;
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $token);
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceVerifyJWT($jwtService, $email, $token);
        $usersService = $this->createUsersService();
        $this->mockUsersServiceGetUserByEmail($usersService, new ModelNotFoundException(UserModelInterface::class), $email);

        $this->expectException(NotAuthenticatedException::class);

        $this->createPasswordResetController()->verifyToken($request, $usersService, $jwtService);
    }

    /**
     * @return void
     */
    public function testGetResetTokenFromRequest(): void
    {
        $token = $this->getFaker()->uuid;
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $token);

        $this->assertEquals(
            $token,
            $this->runReflectionMethod(
                $this->createPasswordResetController(),
                'getResetTokenFromRequest',
                [$request]
            )
        );
    }

    /**
     * @return void
     */
    public function testGetResetTokenFromRequestWithoutToken(): void
    {
        $this->expectException(ValidationException::class);

        $this->runReflectionMethod(
            $this->createPasswordResetController(),
            'getResetTokenFromRequest',
            [$this->createRequest()]
        );
    }

    /**
     * @return void
     */
    public function testResetPassword(): void
    {
        $password = $this->createValidPassword();
        $token = $this->getFaker()->uuid;
        $request = $this->createRequest();
        $request->offsetSet('password', $password);
        $request->offsetSet('passwordConfirm', $password);
        $request->offsetSet('resetToken', $token);
        $email = $this->getFaker()->safeEmail;
        $user = $this->createUserModel();
        $usersService = $this->createUsersService();
        $this->mockUsersServiceGetUserByEmail($usersService, $user, $email);
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceVerifyJWT($jwtService, $email, $token);

        $this->assertEquals(
            $this->createJsonResponse($this->createJsonResponseData(), 204),
            $this->createPasswordResetController()->resetPassword($request, $usersService, $jwtService)
        );
        $this->assertUsersServiceEditUser($usersService, $user, ['password' => $password]);
    }

    /**
     * @return void
     */
    public function testResetPasswordWithoutToken(): void
    {
        $password = $this->createValidPassword();
        $request = $this->createRequest();
        $request->offsetSet('password', $password);
        $request->offsetSet('passwordConfirm', $password);

        $this->expectException(ValidationException::class);

        $this->createPasswordResetController()
            ->resetPassword($request, $this->createUsersService(), $this->createJWTService());
    }

    /**
     * @return void
     */
    public function testResetPasswordWithoutPassword(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $this->getFaker()->uuid);
        $request->offsetSet('passwordConfirm', $this->getFaker()->password);

        $this->expectException(ValidationException::class);

        $this->createPasswordResetController()
            ->resetPassword($request, $this->createUsersService(), $this->createJWTService());
    }

    /**
     * @return void
     */
    public function testResetPasswordWithoutPasswordConfirm(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $this->getFaker()->uuid);
        $request->offsetSet('password', $this->createValidPassword());

        $this->expectException(ValidationException::class);

        $this->createPasswordResetController()
            ->resetPassword($request, $this->createUsersService(), $this->createJWTService());
    }

    /**
     * @return void
     */
    public function testResetPasswordWithoutMatchingPasswords(): void
    {
        $password = $this->createValidPassword();
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $this->getFaker()->uuid);
        $request->offsetSet('password', $password);
        $request->offsetSet('passwordConfirm', $password . $this->getFaker()->word);

        $this->expectException(ValidationException::class);

        $this->createPasswordResetController()
            ->resetPassword($request, $this->createUsersService(), $this->createJWTService());
    }

    /**
     * @return void
     */
    public function testResetPasswordWithInvalidPasswordLength(): void
    {
        $password = $this->getFaker()->password(1, 7);
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $this->getFaker()->uuid);
        $request->offsetSet('password', $password);
        $request->offsetSet('passwordConfirm', $password);

        $this->expectException(ValidationException::class);

        $this->createPasswordResetController()
            ->resetPassword($request, $this->createUsersService(), $this->createJWTService());
    }

    /**
     * @return void
     */
    public function testResetPasswordWithInvalidToken(): void
    {
        $password = $this->createValidPassword();
        $resetToken = $this->getFaker()->uuid;
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $resetToken);
        $request->offsetSet('password', $password);
        $request->offsetSet('passwordConfirm', $password);
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceVerifyJWT($jwtService, new NotAuthenticatedException(), $resetToken);

        $this->expectException(NotAuthenticatedException::class);

        $this->createPasswordResetController()->resetPassword($request, $this->createUsersService(), $jwtService);
    }

    /**
     * @return void
     */
    public function testResetPasswordWithoutUser(): void
    {
        $password = $this->createValidPassword();
        $resetToken = $this->getFaker()->uuid;
        $email = $this->getFaker()->safeEmail;
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $resetToken);
        $request->offsetSet('password', $password);
        $request->offsetSet('passwordConfirm', $password);
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceVerifyJWT($jwtService, $email, $resetToken);
        $usersService = $this->createUsersService();
        $this->mockUsersServiceGetUserByEmail($usersService, new ModelNotFoundException(UserModelInterface::class), $email);

        $this->expectException(NotAuthenticatedException::class);

        $this->createPasswordResetController()->resetPassword($request, $usersService, $jwtService);
    }

    /**
     * @return void
     */
    public function testGetUserFromToken(): void
    {
        $token = $this->getFaker()->uuid;
        $user = $this->createUserModel();
        $email = $this->getFaker()->safeEmail;
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceVerifyJWT($jwtService, $email, $token);
        $usersService = $this->createUsersService();
        $this->mockUsersServiceGetUserByEmail($usersService, $user, $email);

        $this->assertEquals(
            $user,
            $this->runReflectionMethod(
                $this->createPasswordResetController(),
                'getUserFromToken',
                [$usersService, $jwtService, $token]
            )
        );
    }

    /**
     * @return void
     */
    public function testGetUserFromTokenWithInvalidToken(): void
    {
        $token = $this->getFaker()->uuid;
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceVerifyJWT($jwtService, new NotAuthenticatedException(), $token);

        $this->expectException(NotAuthenticatedException::class);

        $this->runReflectionMethod(
            $this->createPasswordResetController(),
            'getUserFromToken',
            [$this->createUsersService(), $jwtService, $token]
        );
    }

    /**
     * @return void
     */
    public function testGetUserFromTokenWithoutUser(): void
    {
        $token = $this->getFaker()->uuid;
        $email = $this->getFaker()->safeEmail;
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceVerifyJWT($jwtService, $email, $token);
        $usersService = $this->createUsersService();
        $this->mockUsersServiceGetUserByEmail($usersService, new ModelNotFoundException(UserModelInterface::class), $email);

        $this->expectException(NotAuthenticatedException::class);

        $this->runReflectionMethod(
            $this->createPasswordResetController(),
            'getUserFromToken',
            [$usersService, $jwtService, $token]
        );
    }

    /**
     * @return void
     */
    public function testGetRequestParametersForPasswordReset(): void
    {
        $resetToken = $this->getFaker()->uuid;
        $password = $this->createValidPassword();
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $resetToken);
        $request->offsetSet('password', $password);
        $request->offsetSet('passwordConfirm', $password);

        $this->assertEquals(
            [$resetToken, $password],
            $this->runReflectionMethod(
                $this->createPasswordResetController(),
                'getRequestParametersForPasswordReset',
                [$request]
            )
        );
    }

    /**
     * @return void
     */
    public function testGetRequestParametersForPasswordResetWithoutResetToken(): void
    {
        $password = $this->createValidPassword();
        $request = $this->createRequest();
        $request->offsetSet('password', $password);
        $request->offsetSet('passwordConfirm', $password);

        $this->expectException(ValidationException::class);

        $this->runReflectionMethod(
            $this->createPasswordResetController(),
            'getRequestParametersForPasswordReset',
            [$request]
        );
    }

    /**
     * @return void
     */
    public function testGetRequestParametersForPasswordResetWithoutPassword(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $this->getFaker()->uuid);
        $request->offsetSet('passwordConfirm', $this->createValidPassword());

        $this->expectException(ValidationException::class);

        $this->runReflectionMethod(
            $this->createPasswordResetController(),
            'getRequestParametersForPasswordReset',
            [$request]
        );
    }

    /**
     * @return void
     */
    public function testGetRequestParametersForPasswordResetWithInvalidPasswordLength(): void
    {
        $password = $this->getFaker()->password(1, 7);
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $this->getFaker()->uuid);
        $request->offsetSet('password', $password);
        $request->offsetSet('passwordConfirm', $password);

        $this->expectException(ValidationException::class);

        $this->runReflectionMethod(
            $this->createPasswordResetController(),
            'getRequestParametersForPasswordReset',
            [$request]
        );
    }

    /**
     * @return void
     */
    public function testGetRequestParametersForPasswordResetWithoutPasswordConfirm(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $this->getFaker()->uuid);
        $request->offsetSet('password', $this->createValidPassword());

        $this->expectException(ValidationException::class);

        $this->runReflectionMethod(
            $this->createPasswordResetController(),
            'getRequestParametersForPasswordReset',
            [$request]
        );
    }

    /**
     * @return void
     */
    public function testGetRequestParametersForPasswordResetWithoutMatchingPasswordConfirm(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('resetToken', $this->getFaker()->uuid);
        $request->offsetSet('password', $this->createValidPassword());
        $request->offsetSet('passwordConfirm', $this->createValidPassword());

        $this->expectException(ValidationException::class);

        $this->runReflectionMethod(
            $this->createPasswordResetController(),
            'getRequestParametersForPasswordReset',
            [$request]
        );
    }

    //endregion

    //region Mocks

    /**
     * @param string|null $tokenPlaceHolder
     *
     * @return PasswordResetController
     */
    private function createPasswordResetController(string $tokenPlaceHolder = null): PasswordResetController
    {
        return new PasswordResetController($tokenPlaceHolder ?: $this->getFaker()->uuid);
    }

    //endregion
}