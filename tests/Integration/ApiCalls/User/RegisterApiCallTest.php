<?php

use App\Http\Controllers\User\UsersController;
use App\Models\User\UserModelInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use LaravelDoctrine\Migrations\Testing\DatabaseMigrations;
use Test\ApiHelper;
use Test\ModelHelper;
use Test\ResponseHelper;
use Test\UserHelper;

/**
 * Class UsersApiCallsTest
 */
class RegisterApiCallTest extends IntegrationTestCase
{

    use ApiHelper;
    use DatabaseMigrations;
    use ModelHelper;
    use ResponseHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testRegister(): void
    {
        $email = $this->getFaker()->email;
        $password = $this->getFaker()->password;

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_REGISTER),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_EMAIL          => $email,
                UserModelInterface::PROPERTY_PASSWORD       => $password,
                UsersController::USER_DATA_PASSWORD_CONFIRM => $password,
            ]
        );

        $this->assertResponseStatus(Response::HTTP_CREATED);
        $this->assertNotEmpty($this->getAuthorizationHeader($response));

        $responseData = $response->getData(true);
        $this->assertArrayHasKey(UsersController::RESPONSE_PARAMETER_USER, $responseData);
        $this->assertEquals(
            $email,
            $responseData[UsersController::RESPONSE_PARAMETER_USER][UserModelInterface::PROPERTY_EMAIL]
        );
    }

    /**
     * @retrn void
     */
    public function testRegisterEmptyData(): void
    {
        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_REGISTER),
            Request::METHOD_POST
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEmpty($this->getAuthorizationHeader($response));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.required', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_PASSWORD, $responseData);
        $this->assertEquals('validation.required', \reset($responseData[UserModelInterface::PROPERTY_PASSWORD]));

        $this->assertArrayHasKey(UsersController::USER_DATA_PASSWORD_CONFIRM, $responseData);
        $this->assertEquals('validation.required', \reset($responseData[UsersController::USER_DATA_PASSWORD_CONFIRM]));
    }

    /**
     * @retrn void
     */
    public function testRegisterInvalidEmail(): void
    {
        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_REGISTER),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_EMAIL => $this->getFaker()->uuid,
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEmpty($this->getAuthorizationHeader($response));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.email', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
    }

    /**
     * @retrn void
     */
    public function testRegisterEmptyPasswordConfirmAndInvalidPassword(): void
    {
        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_REGISTER),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_PASSWORD       => $this->getFaker()->password(1, 7),
                UsersController::USER_DATA_PASSWORD_CONFIRM => $this->getFaker()->password,
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEmpty($this->getAuthorizationHeader($response));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_PASSWORD, $responseData);
        $this->assertEquals('validation.min.string', \reset($responseData[UserModelInterface::PROPERTY_PASSWORD]));
        $this->assertArrayHasKey(UsersController::USER_DATA_PASSWORD_CONFIRM, $responseData);
        $this->assertEquals('validation.same', \reset($responseData[UsersController::USER_DATA_PASSWORD_CONFIRM]));
    }

    /**
     * @return void
     */
    public function testRegisterWithExistingEmail(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_REGISTER),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_EMAIL => $user->getEmail(),
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEmpty($this->getAuthorizationHeader($response));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.unique', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
    }

    //endregion
}
