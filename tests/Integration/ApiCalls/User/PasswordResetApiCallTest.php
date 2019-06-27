<?php

use App\Http\Controllers\User\PasswordResetController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
            ['email' => $user->getEmail()]
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
            ['email' => $this->getFaker()->safeEmail]
        );

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertEmpty($response->getData(true));
        $this->assertEmpty($this->getEmailService()->getQueuedEmailsByIdentifier('password-reset'));
    }

    //endregion

    //region Assertions

    private function assertQueuedEmail(string $email): PasswordResetApiCallTest
    {
        $queuedEmails = $this->getEmailService()->getQueuedEmailsByIdentifier('password-reset');
        $this->assertEquals($email, $queuedEmails[0]['recipient']);
        $this->assertNotEmpty($queuedEmails[0]['context']['resetToken']);

        return $this;
    }

    //endregion
}