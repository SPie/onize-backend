<?php

use App\Http\Controllers\User\PasswordResetController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use SPie\LaravelJWT\Contracts\JWTHandler;
use Test\ApiHelper;
use Test\ModelHelper;
use Test\UserHelper;

/**
 * Class ResetPasswordApiCallTest
 */
final class PasswordResetApiCallTest extends IntegrationTestCase
{
    use ApiHelper;
    use ModelHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testStart(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_START),
            Request::METHOD_POST,
            [
                'email'     => $user->getEmail(),
                'finishUrl' => $this->getFaker()->url,
            ]
        );

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertEmpty($response->getData(true));
        $this->assertQueuedEmail($user->getEmail());
    }

    /**
     * @return void
     */
    public function testStartWithoutEmail(): void
    {
        $response = $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_START),
            Request::METHOD_POST
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $data = $response->getData(true);
        $this->assertEquals('validation.required', \reset($data['email']));
        $this->assertEmpty($this->getEmailService()->getQueuedEmailsByIdentifier('password-reset'));
    }

    /**
     * @return void
     */
    public function testStartWithInvalidEmail(): void
    {
        $response = $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_START),
            Request::METHOD_POST,
            ['email' => $this->getFaker()->uuid]
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $data = $response->getData(true);
        $this->assertEquals('validation.email', \reset($data['email']));
        $this->assertEmpty($this->getEmailService()->getQueuedEmailsByIdentifier('password-reset'));
    }
    /**
     * @return void
     */
    public function testStartWithoutUser(): void
    {
        $response = $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_START),
            Request::METHOD_POST,
            [
                'email'     => $this->getFaker()->safeEmail,
                'finishUrl' => $this->getFaker()->url,
            ]
        );

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertEmpty($response->getData(true));
        $this->assertEmpty($this->getEmailService()->getQueuedEmailsByIdentifier('password-reset'));
    }

    /**
     * @return void
     */
    public function testStarWithoutFinishUrl(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_START),
            Request::METHOD_POST,
            [
                'email' => $user->getEmail(),
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $data = $response->getData(true);
        $this->assertEquals('validation.required', \reset($data['finishUrl']));
        $this->assertEmpty($this->getEmailService()->getQueuedEmailsByIdentifier('password-reset'));
    }

    /**
     * @return void
     */
    public function testVerifyToken(): void
    {
        $response = $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_VERIFY_TOKEN),
            Request::METHOD_GET,
            ['resetToken' => $this->createJWTToken($this->createUsers()->first())->getJWT()]
        );

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertEmpty($response->getData(true));
    }

    /**
     * @return void
     */
    public function testVerifyTokenWithoutToken(): void
    {
        $response = $this->doApiCall($this->getUrl(PasswordResetController::ROUTE_NAME_VERIFY_TOKEN));

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $data = $response->getData(true);
        $this->assertEquals('validation.required', \reset($data['resetToken']));
    }

    /**
     * @return void
     */
    public function testVerifyTokenWithInvalidToken(): void
    {
        $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_VERIFY_TOKEN),
            Request::METHOD_GET,
            ['resetToken' => $this->getFaker()->uuid]
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return void
     */
    public function testVerifyTokenWithInvalidUser(): void
    {
        $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_VERIFY_TOKEN),
            Request::METHOD_GET,
            ['resetToken' => $this->app->get(JWTHandler::class)->createJWT($this->getFaker()->safeEmail, [])->getJWT()]
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return void
     */
    public function testResetPassword(): void
    {
        $password = $this->createValidPassword();
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_RESET_PASSWORD),
            Request::METHOD_PATCH,
            [
                'resetToken'      => $this->createJWTToken($user)->getJWT(),
                'password'        => $password,
                'passwordConfirm' => $password,
            ]
        );

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertEmpty($response->getData(true));
        $this->assertTrue(Hash::check($password, $user->getPassword()));
    }

    /**
     * @return void
     */
    public function testResetPasswordWithoutResetToken(): void
    {
        $password = $this->createValidPassword();

        $response = $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_RESET_PASSWORD),
            Request::METHOD_PATCH,
            [
                'password'        => $password,
                'passwordConfirm' => $password,
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', \reset($responseData['resetToken']));
    }

    /**
     * @return void
     */
    public function testResetPasswordWithoutPassword(): void
    {
        $response = $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_RESET_PASSWORD),
            Request::METHOD_PATCH,
            [
                'resetToken'      => $this->createJWTToken($this->createUsers()->first())->getJWT(),
                'passwordConfirm' => $this->createValidPassword(),
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', \reset($responseData['password']));
    }

    /**
     * @return void
     */
    public function testResetPasswordWithInvalidPasswordLength(): void
    {
        $password = $this->getFaker()->password(1, 7);

        $response = $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_RESET_PASSWORD),
            Request::METHOD_PATCH,
            [
                'resetToken'      => $this->createJWTToken($this->createUsers()->first())->getJWT(),
                'password'        => $password,
                'passwordConfirm' => $password,
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.min.string', \reset($responseData['password']));
    }

    /**
     * @return void
     */
    public function testResetPasswordWithoutPasswordConfirm(): void
    {
        $response = $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_RESET_PASSWORD),
            Request::METHOD_PATCH,
            [
                'resetToken'      => $this->createJWTToken($this->createUsers()->first())->getJWT(),
                'password'        => $this->createValidPassword(),
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', \reset($responseData['passwordConfirm']));
    }

    /**
     * @return void
     */
    public function testResetPasswordWithoutMatchingPasswords(): void
    {
        $response = $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_RESET_PASSWORD),
            Request::METHOD_PATCH,
            [
                'resetToken'      => $this->createJWTToken($this->createUsers()->first())->getJWT(),
                'password'        => $this->createValidPassword(),
                'passwordConfirm' => $this->createValidPassword(),
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.same', \reset($responseData['passwordConfirm']));
    }

    /**
     * @return void
     */
    public function testResetPasswordWithInvalidResetToken(): void
    {
        $password = $this->createValidPassword();

        $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_RESET_PASSWORD),
            Request::METHOD_PATCH,
            [
                'resetToken'      => $this->getFaker()->uuid,
                'password'        => $password,
                'passwordConfirm' => $password,
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return void
     */
    public function testResetPasswordWithoutUser(): void
    {
        $password = $this->createValidPassword();

        $this->doApiCall(
            $this->getUrl(PasswordResetController::ROUTE_NAME_RESET_PASSWORD),
            Request::METHOD_PATCH,
            [
                'resetToken'      => $this->app->get(JWTHandler::class)
                    ->createJWT($this->getFaker()->safeEmail, [], 15)
                    ->getJWT(),
                'password'        => $password,
                'passwordConfirm' => $password,
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    //endregion

    //region Assertions

    private function assertQueuedEmail(string $email): PasswordResetApiCallTest
    {
        $queuedEmails = $this->getEmailService()->getQueuedEmailsByIdentifier('passwordReset');
        $this->assertEquals($email, $queuedEmails[0]['recipient']);
        $this->assertNotEmpty($queuedEmails[0]['context']['resetToken']);

        return $this;
    }

    //endregion
}
