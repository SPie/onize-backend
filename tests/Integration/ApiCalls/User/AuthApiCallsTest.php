<?php

use App\Http\Controllers\User\UsersController;
use App\Models\User\RefreshTokenModel;
use App\Models\User\UserModelInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use LaravelDoctrine\Migrations\Testing\DatabaseMigrations;
use SPie\LaravelJWT\Contracts\TokenBlacklist;
use Test\ApiHelper;
use Test\AuthIntegrationHelper;
use Test\ModelHelper;
use Test\RequestResponseHelper;
use Test\UserHelper;

/**
 * Class AuthApiCallsTest
 */
class AuthApiCallsTest extends IntegrationTestCase
{
    use ApiHelper;
    use AuthIntegrationHelper;
    use DatabaseMigrations;
    use ModelHelper;
    use RequestResponseHelper;
    use UserHelper;

    const BEARER_AUTHORIZATION = 'Authorization';

    //region Tests

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testLogin(): void
    {
        $password = $this->getFaker()->password();

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_LOGIN),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_EMAIL    => $this->createUsers(
                    1,
                    [UserModelInterface::PROPERTY_PASSWORD => Hash::make($password)]
                )->first()->getEmail(),
                UserModelInterface::PROPERTY_PASSWORD => $password,
            ]
        );

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertNotEmpty($this->getAuthorizationHeader($response));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testLoginWithoutCredentials(): void
    {
        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_LOGIN),
            Request::METHOD_POST
        );

        $this->assertResponseStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEmpty($this->getAuthorizationHeader($response));

        $responseData = $response->getData(true);

        $this->assertArrayHasKey(UserModelInterface::PROPERTY_EMAIL, $responseData);
        $this->assertEquals('validation.required', \reset($responseData[UserModelInterface::PROPERTY_EMAIL]));
        $this->assertArrayHasKey(UserModelInterface::PROPERTY_PASSWORD, $responseData);
        $this->assertEquals('validation.required', \reset($responseData[UserModelInterface::PROPERTY_PASSWORD]));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testLoginWithInvalidEmail(): void
    {
        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_LOGIN),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_EMAIL    => $this->getFaker()->safeEmail,
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password(),
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertEmpty($this->getAuthorizationHeader($response));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testLoginWithInvalidPassword(): void
    {
        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_LOGIN),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_EMAIL    => $this->createUsers()->first()->getEmail(),
                UserModelInterface::PROPERTY_PASSWORD => $this->getFaker()->password(),
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertEmpty($this->getAuthorizationHeader($response));
    }

    /**
     * @return void
     */
    public function testLoginWithRefreshToken(): void
    {
        $password = $this->getFaker()->password();

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_LOGIN),
            Request::METHOD_POST,
            [
                UserModelInterface::PROPERTY_EMAIL    => $this->createUsers(
                    1,
                    [UserModelInterface::PROPERTY_PASSWORD => Hash::make($password)]
                )->first()->getEmail(),
                UserModelInterface::PROPERTY_PASSWORD => $password,
                'remember' => true
            ]
        );

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertNotEmpty($this->getAuthorizationHeader($response));
        $this->assertNotEmpty($this->getRefreshTokenCookie($response));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testLogout(): void
    {
        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_LOGOUT),
            Request::METHOD_POST,
            [],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertEmpty($this->getAuthorizationHeader($response));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testLogoutWithoutAuthenticatedUser(): void
    {
        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_LOGOUT),
            Request::METHOD_POST
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertEmpty($this->getAuthorizationHeader($response));
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testAuthenticatedUser(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_AUTHENTICATED),
            Request::METHOD_GET,
            [],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseOk();

        $responseData = $response->getData(true);
        $this->assertArrayHasKey(UsersController::RESPONSE_PARAMETER_USER, $responseData);
        $this->assertEquals($user->toArray(), $responseData[UsersController::RESPONSE_PARAMETER_USER]);
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function testAuthenticatedUserWithoutUser(): void
    {
        $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_AUTHENTICATED),
            Request::METHOD_GET
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testAuthenticatedUserWithBlacklistedToken(): void
    {
        $user = $this->createUsers()->first();
        $jwt = $this->createJWTToken($user);

        $this->app->get(TokenBlacklist::class)->revoke($jwt);

        $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_AUTHENTICATED),
            Request::METHOD_GET,
            [],
            null,
            [
                $this->getTokenKey() => $this->getTokenPrefix() . ' ' . $jwt->getJWT(),
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testAuthenticatedUserWithRevokedToken(): void
    {
        $user = $this->createUsers()->first();

        $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_AUTHENTICATED),
            Request::METHOD_GET,
            [],
            null,
            [
                $this->getTokenKey() => $this->getTokenPrefix() . ' ' . $this->createJWTToken(
                        $user,
                        $this->createRefreshTokens(
                            1,
                            [
                                RefreshTokenModel::PROPERTY_USER => $user,
                                RefreshTokenModel::PROPERTY_VALID_UNTIL => (new \DateTime())->sub(new \DateInterval('P1D')),
                            ]
                        )->first()
                    )->getJWT(),
            ]
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRefresh(): void
    {
        $refreshToken = $this->createRefreshTokens(
            1,
            [
                RefreshTokenModel::PROPERTY_VALID_UNTIL => (new \DateTime())->add(new \DateInterval('P1D'))
            ]
        )->first();
        $user = $refreshToken->getUser();
        $jwt = $this->createJWTToken($user, $refreshToken);

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_REFRESH_ACCESS_TOKEN),
            Request::METHOD_GET,
            [],
            $this->createRefreshTokenCookie($jwt)
        );

        $this->assertResponseStatus(Response::HTTP_NO_CONTENT);
        $this->assertNotEmpty($this->getAuthorizationHeader($response));
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRefreshWithoutRefreshToken(): void
    {
        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_REFRESH_ACCESS_TOKEN),
            Request::METHOD_GET
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertEmpty($this->getAuthorizationHeader($response));
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testRefreshWithRevokedRefreshToken(): void
    {
        $refreshToken = $this->createRefreshTokens(
            1,
            [
                RefreshTokenModel::PROPERTY_VALID_UNTIL => (new \DateTime())->sub(new \DateInterval('P1D'))
            ]
        )->first();
        $user = $refreshToken->getUser();
        $jwt = $this->createJWTToken($user, $refreshToken);

        $response = $this->doApiCall(
            URL::route(UsersController::ROUTE_NAME_REFRESH_ACCESS_TOKEN),
            Request::METHOD_GET,
            [],
            $this->createRefreshTokenCookie($jwt)
        );

        $this->assertResponseStatus(Response::HTTP_UNAUTHORIZED);
        $this->assertEmpty($this->getAuthorizationHeader($response));
    }

    //endregion
}
