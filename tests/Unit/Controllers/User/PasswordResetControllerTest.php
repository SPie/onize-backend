<?php

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