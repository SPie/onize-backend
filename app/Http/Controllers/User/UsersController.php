<?php

namespace App\Http\Controllers\User;

use App\Exceptions\Auth\NotAuthenticatedException;
use App\Http\Controllers\Controller;
use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelInterface;
use App\Services\JWT\JWTService;
use App\Services\Security\LoginThrottlingServiceInterface;
use App\Services\User\UsersServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UsersController
 *
 * @package App\Http\Controllers\User
 */
class UsersController extends Controller
{
    const ROUTE_NAME_REGISTER             = 'users.register';
    const ROUTE_NAME_CHANGE_PASSWORD      = 'users.changePassword';
    const ROUTE_NAME_LOGIN                = 'users.login';
    const ROUTE_NAME_LOGOUT               = 'users.logout';
    const ROUTE_NAME_AUTHENTICATED        = 'users.authenticated';
    const ROUTE_NAME_REFRESH_ACCESS_TOKEN = 'users.refreshAccessToken';

    const RESPONSE_PARAMETER_USER  = 'user';

    const REQUEST_PARAMETER_REMEMBER   = 'remember';
    const REQUEST_PARAMETER_IP_ADDRESS = 'ipAddress';

    const USER_DATA_PASSWORD_CONFIRM = 'passwordConfirm';
    const USER_DATA_PASSWORD_CURRENT = 'currentPassword';

    /**
     * @var UsersServiceInterface
     */
    private $usersService;

    /**
     * UsersController constructor.
     *
     * @param UsersServiceInterface $usersService
     */
    public function __construct(UsersServiceInterface $usersService)
    {
        $this->usersService = $usersService;
    }

    /**
     * @return UsersServiceInterface
     */
    protected function getUserService(): UsersServiceInterface
    {
        return $this->usersService;
    }

    //region Controller actions

    /**
     * @param Request    $request
     * @param JWTService $jwtService
     *
     * @return JsonResponse|Response
     *
     * @throws ValidationException
     */
    public function register(Request $request, JWTService $jwtService): JsonResponse
    {
        $user = $this->getUserService()->createUser(
            $this->validate($request, \array_merge($this->getEmailValidators(), $this->getPasswordValidators()))
        );

        return $jwtService->issueTokens(
            $user,
            $this->createResponse(
                [
                    self::RESPONSE_PARAMETER_USER => $user,
                ],
                Response::HTTP_CREATED
            ),
            $request->get(self::REQUEST_PARAMETER_REMEMBER, false)
        );
    }

    /**
     * @param Request                         $request
     * @param JWTService                      $jwtService
     * @param LoginThrottlingServiceInterface $loginThrottlingService
     *
     * @return JsonResponse|Response
     *
     * @throws ValidationException
     */
    public function login(
        Request $request,
        JWTService $jwtService,
        LoginThrottlingServiceInterface $loginThrottlingService
    ): JsonResponse {
        $requestParameters = $this->validate(
            $request,
            [
                UserModelInterface::PROPERTY_EMAIL    => ['required'],
                UserModelInterface::PROPERTY_PASSWORD => ['required'],
            ]
        );

        if (
            $loginThrottlingService->isLoginBlocked(
                $request->ip(),
                $requestParameters[UserModelInterface::PROPERTY_EMAIL]
            )
        ) {
            throw new NotAuthenticatedException();
        }

        return $jwtService->login(
            $this->createResponse([], Response::HTTP_NO_CONTENT),
            $requestParameters,
            $request->get(self::REQUEST_PARAMETER_REMEMBER, false)
        );
    }

    /**
     * @param JWTService $jwtService
     *
     * @return JsonResponse|Response
     */
    public function logout(JWTService $jwtService): JsonResponse
    {
        return $jwtService->logout($this->createResponse([], Response::HTTP_NO_CONTENT));
    }

    /**
     * @param JWTService $jwtService
     *
     * @return JsonResponse
     */
    public function authenticatedUser(JWTService $jwtService): JsonResponse
    {
        return $this->createResponse([
            self::RESPONSE_PARAMETER_USER => $jwtService->getAuthenticatedUser()
        ]);
    }

    /**
     * @param JWTService $jwtService
     *
     * @return JsonResponse|Response
     */
    public function refreshAccessToken(JWTService $jwtService): JsonResponse
    {
        return $jwtService->refreshAccessToken($this->createResponse([], Response::HTTP_NO_CONTENT));
    }

    /**
     * @param Request    $request
     * @param JWTService $jwtService
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function changePassword(Request $request, JWTService $jwtService): JsonResponse
    {
        return $this->createResponse([
            self::RESPONSE_PARAMETER_USER => $this->getUserService()->editUser(
                $jwtService->getAuthenticatedUser(),
                $this->validate(
                    $request,
                    $this->getPasswordValidators($jwtService->getAuthenticatedUser()->getAuthPassword())
                )
            ),
        ]);
    }

    //endregion

    /**
     * @return array
     */
    protected function getEmailValidators(): array
    {
        return [
            UserModelInterface::PROPERTY_EMAIL => [
                'email',
                'required',
                Rule::unique(UserDoctrineModel::class, UserModelInterface::PROPERTY_EMAIL),
            ],
        ];
    }

    /**
     * @param string|null $currentPassword
     *
     * @return array
     */
    protected function getPasswordValidators(string $currentPassword = null): array
    {
        $rules = [
            UserModelInterface::PROPERTY_PASSWORD       => [
                'min:8',
                'required',
            ],
            UsersController::USER_DATA_PASSWORD_CONFIRM => [
                'required',
                'same:' . UserModelInterface::PROPERTY_PASSWORD,
            ],
        ];

        if (!empty($currentPassword)) {
            $rules[UsersController::USER_DATA_PASSWORD_CURRENT] = [
                'required',
                function ($attribute, $value, $fail) use ($currentPassword) {
                    if (!Hash::check($value, $currentPassword)) {
                        $fail('validation.password.current');
                    }

                    return true;
                }
            ];
        }

        return $rules;
    }
}
