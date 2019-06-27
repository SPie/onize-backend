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
        $request = $this->createRequest();
        $request->offsetSet('email', $email);
        $user = $this->createUserModel();
        $usersService = $this->createUsersService();
        $this->mockUsersServiceGetUserByEmail($usersService, $user, $email);
        $jwtService = $this->createJWTService();
        $this->mockJWTServiceCreateJWT($jwtService, $token, $user, 15);
        $emailService = $this->createEmailService();

        $this->assertJsonResponse(
            $this->createJsonResponse($this->createJsonResponseData(), 204),
            $this->createPasswordResetController()->start($request, $usersService, $jwtService, $emailService)
        );
        $this->assertEmailServiceQueueEmail($emailService, 'password-reset', $email, ['resetToken' => $token]);
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
    public function testValidateUserEmail(): void
    {
        $email = $this->getFaker()->safeEmail;
        $usersService = $this->createUsersService();
        $passwordResetController = $this->createPasswordResetController();

        $this->assertEquals(
            $passwordResetController,
            $this->runReflectionMethod(
                $passwordResetController,
                'validateUserEmail',
                [$usersService, $email]
            )
        );
        $this->assertUsersServiceGetUserByEmail($usersService, $email);
    }

    /**
     * @return void
     */
    public function testValidateUserEmailWithoutUser(): void
    {
        $email = $this->getFaker()->safeEmail;
        $usersService = $this->createUsersService();
        $this->mockUsersServiceGetUserByEmail($usersService, new ModelNotFoundException(UserModelInterface::class), $email);

        $this->expectException(NotAuthenticatedException::class);

        $this->runReflectionMethod(
            $this->createPasswordResetController(),
            'validateUserEmail',
            [$usersService, $email]
        );
    }

    //endregion

    //region Mocks

    /**
     * @return PasswordResetController
     */
    private function createPasswordResetController(): PasswordResetController
    {
        return new PasswordResetController();
    }

    //endregion
}