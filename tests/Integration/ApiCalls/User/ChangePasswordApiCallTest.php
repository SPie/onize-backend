<?php

use App\Http\Controllers\User\UsersController;
use App\Models\User\UserModelInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use LaravelDoctrine\Migrations\Testing\DatabaseMigrations;
use Test\ApiHelper;
use Test\ModelHelper;
use Test\ResponseHelper;
use Test\UserHelper;

/**
 * Class ChangePasswordApiCallTest
 */
class ChangePasswordApiCallTest extends IntegrationTestCase
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
    public function testChangePassword(): void
    {
        $currentPassword = $this->getFaker()->password(8);
        $password = $this->getFaker()->password(8);

        $data = [
            UserModelInterface::PROPERTY_PASSWORD       => $password,
            UsersController::USER_DATA_PASSWORD_CONFIRM => $password,
            UsersController::USER_DATA_PASSWORD_CURRENT => $currentPassword,
        ];

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_CHANGE_PASSWORD),
            Request::METHOD_PATCH,
            $data,
            null,
            $this->createAuthHeader(
                $this->createUsers(1, [UserModelInterface::PROPERTY_PASSWORD => Hash::make($currentPassword)])->first()
            )
        );

        $this->assertResponseOk();
        $this->assertNotEmpty($this->getAuthorizationHeader($response));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UsersController::RESPONSE_PARAMETER_USER, $responseData);
    }

    /**
     * @return void
     */
    public function testChangePasswordEmptyPassword(): void
    {
        $currentPassword = $this->getFaker()->password(8);

        $data = [
            UsersController::USER_DATA_PASSWORD_CONFIRM => $this->getFaker()->password(8),
            UsersController::USER_DATA_PASSWORD_CURRENT => $currentPassword,
        ];

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_CHANGE_PASSWORD),
            Request::METHOD_PATCH,
            $data,
            null,
            $this->createAuthHeader(
                $this->createUsers(1, [UserModelInterface::PROPERTY_PASSWORD => Hash::make($currentPassword)])->first()
            )
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getAuthorizationHeader($response));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_PASSWORD, $responseData);
        $this->assertEquals('validation.required', \reset($responseData[UserModelInterface::PROPERTY_PASSWORD]));
    }

    /**
     * @return void
     */
    public function testChangePasswordInvalidPassword(): void
    {
        $currentPassword = $this->getFaker()->password(8);
        $password = $this->getFaker()->password(1, 7);

        $data = [
            UserModelInterface::PROPERTY_PASSWORD       => $password,
            UsersController::USER_DATA_PASSWORD_CONFIRM => $password,
            UsersController::USER_DATA_PASSWORD_CURRENT => $currentPassword,
        ];

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_CHANGE_PASSWORD),
            Request::METHOD_PATCH,
            $data,
            null,
            $this->createAuthHeader(
                $this->createUsers(1, [UserModelInterface::PROPERTY_PASSWORD => Hash::make($currentPassword)])->first()
            )
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getAuthorizationHeader($response));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_PASSWORD, $responseData);
        $this->assertEquals(
            'validation.min.string',
            \reset($responseData[UserModelInterface::PROPERTY_PASSWORD])
        );
    }

    /**
     * @return void
     */
    public function testChangePasswordInvalidCurrentPassword(): void
    {
        $currentPassword = $this->getFaker()->password(8);
        $password = $this->getFaker()->password(8);

        $data = [
            UserModelInterface::PROPERTY_PASSWORD       => $password,
            UsersController::USER_DATA_PASSWORD_CONFIRM => $password,
            UsersController::USER_DATA_PASSWORD_CURRENT => $currentPassword,
        ];

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_CHANGE_PASSWORD),
            Request::METHOD_PATCH,
            $data,
            null,
            $this->createAuthHeader(
                $this->createUsers()->first()
            )
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getAuthorizationHeader($response));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UsersController::USER_DATA_PASSWORD_CURRENT, $responseData);
        $this->assertEquals(
            'validation.password.current',
            \reset($responseData[UsersController::USER_DATA_PASSWORD_CURRENT])
        );
    }

    /**
     * @return void
     */
    public function testChangePasswordEmptyCurrentPassword(): void
    {
        $password = $this->getFaker()->password(8);

        $data = [
            UserModelInterface::PROPERTY_PASSWORD       => $password,
            UsersController::USER_DATA_PASSWORD_CONFIRM => $password,
        ];

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_CHANGE_PASSWORD),
            Request::METHOD_PATCH,
            $data,
            null,
            $this->createAuthHeader(
                $this->createUsers()->first()
            )
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getAuthorizationHeader($response));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UsersController::USER_DATA_PASSWORD_CURRENT, $responseData);
        $this->assertEquals(
            'validation.required',
            \reset($responseData[UsersController::USER_DATA_PASSWORD_CURRENT])
        );
    }

    /**
     * @return void
     */
    public function testChangePasswordInvalidPasswordConfirm(): void
    {
        $currentPassword = $this->getFaker()->password(8);

        $data = [
            UserModelInterface::PROPERTY_PASSWORD       => $this->getFaker()->password(8),
            UsersController::USER_DATA_PASSWORD_CONFIRM => $this->getFaker()->password(8),
            UsersController::USER_DATA_PASSWORD_CURRENT => $currentPassword,
        ];

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_CHANGE_PASSWORD),
            Request::METHOD_PATCH,
            $data,
            null,
            $this->createAuthHeader(
                $this->createUsers(1, [UserModelInterface::PROPERTY_PASSWORD => Hash::make($currentPassword)])->first()
            )
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getAuthorizationHeader($response));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UsersController::USER_DATA_PASSWORD_CONFIRM, $responseData);
        $this->assertEquals(
            'validation.same',
            \reset($responseData[UsersController::USER_DATA_PASSWORD_CONFIRM])
        );
    }

    /**
     * @return void
     */
    public function testChangePasswordEmptyPasswordConfirm(): void
    {
        $currentPassword = $this->getFaker()->password(8);

        $data = [
            UserModelInterface::PROPERTY_EMAIL          => $this->getFaker()->safeEmail,
            UserModelInterface::PROPERTY_PASSWORD       => $this->getFaker()->password(8),
            UsersController::USER_DATA_PASSWORD_CURRENT => $currentPassword,
        ];

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_CHANGE_PASSWORD),
            Request::METHOD_PATCH,
            $data,
            null,
            $this->createAuthHeader(
                $this->createUsers(1, [UserModelInterface::PROPERTY_PASSWORD => Hash::make($currentPassword)])->first()
            )
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertNotEmpty($this->getAuthorizationHeader($response));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UsersController::USER_DATA_PASSWORD_CONFIRM, $responseData);
        $this->assertEquals(
            'validation.required',
            \reset($responseData[UsersController::USER_DATA_PASSWORD_CONFIRM])
        );
    }

    //endregion
}
