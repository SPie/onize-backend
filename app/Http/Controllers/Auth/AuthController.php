<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User\UserModelInterface;
use App\Services\JWT\JWTService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers\Auth
 */
class AuthController extends Controller
{

    const ROUTE_NAME_LOGIN                  = 'auth.login';
    const ROUTE_NAME_LOGOUT                 = 'auth.logout';
    const ROUTE_NAME_USER                   = 'auth.user';
    const ROUTE_NAME_REFRESH                = 'refresh';
    const ROUTE_NAME_PASSWORD_RESET_REQUEST = 'users.requestPasswordReset';

    const REQUEST_PARAMETER_REMEMBER = 'remember';

    const RESPONSE_PARAMETER_USER = 'user';

    /**
     * @var JWTService
     */
    private $jwtService;

    /**
     * AuthController constructor.
     *
     * @param JWTService $jwtService
     */
    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * @return JWTService
     */
    protected function getJwtService(): JWTService
    {
        return $this->jwtService;
    }

    //region Controller actions

    /**
     * @param Request $request
     *
     * @return JsonResponse|Response
     *
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        return $this->getJwtService()->login(
            $this->createResponse([], Response::HTTP_NO_CONTENT),
            $this->validate(
                $request,
                [
                    UserModelInterface::PROPERTY_EMAIL => [
                        'required',
                    ],
                    UserModelInterface::PROPERTY_PASSWORD => [
                        'required',
                    ],
                ]
            ),
            $request->get(self::REQUEST_PARAMETER_REMEMBER, false)
        );
    }

    /**
     * @return JsonResponse|Response
     */
    public function logout(): JsonResponse
    {
        return $this->getJwtService()->logout($this->createResponse([], Response::HTTP_NO_CONTENT));
    }

    /**
     * @return JsonResponse
     */
    public function authenticatedUser(): JsonResponse
    {
        return $this->createResponse(
            [
                self::RESPONSE_PARAMETER_USER => $this->getJwtService()->getAuthenticatedUser(),
            ]
        );
    }

    /**
     * @return JsonResponse|Response
     */
    public function refreshAccessToken(): JsonResponse
    {
        return $this->getJwtService()->refreshAccessToken(
            $this->createResponse([], Response::HTTP_NO_CONTENT)
        );
    }

    //endregion
}
