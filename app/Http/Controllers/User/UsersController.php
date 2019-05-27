<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelInterface;
use App\Services\JWT\JWTService;
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

    const ROUTE_NAME_REGISTER        = 'users.register';
    const ROUTE_NAME_CHANGE_PASSWORD = 'users.changePassword';

    const RESPONSE_PARAMETER_USERS = 'users';
    const RESPONSE_PARAMETER_USER  = 'user';

    const USER_DATA_PASSWORD_CONFIRM = 'passwordConfirm';
    const USER_DATA_PASSWORD_CURRENT = 'currentPassword';

    /**
     * @var UsersServiceInterface
     */
    private $usersService;

    private $jwtService;

    /**
     * UsersController constructor.
     *
     * @param UsersServiceInterface $usersService
     * @param JWTService            $jwtService
     */
    public function __construct(UsersServiceInterface $usersService, JWTService $jwtService)
    {
        $this->usersService = $usersService;
        $this->jwtService = $jwtService;
    }

    /**
     * @return UsersServiceInterface
     */
    protected function getUserService(): UsersServiceInterface
    {
        return $this->usersService;
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
    public function register(Request $request): JsonResponse
    {
        $user = $this->getUserService()->createUser(
            $this->validate($request, \array_merge($this->getEmailValidators(), $this->getPasswordValidators()))
        );

        return $this->getJwtService()->issueTokens(
            $user,
            $this->createResponse(
                [
                    self::RESPONSE_PARAMETER_USER => $user,
                ],
                Response::HTTP_CREATED
            ),
            $request->get('stayLoggedIn', false)
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function changePassword(Request $request): JsonResponse
    {
        return $this->createResponse([
            self::RESPONSE_PARAMETER_USER => $this->getUserService()->editUser(
                $this->getJwtService()->getAuthenticatedUser(),
                $this->validate(
                    $request,
                    $this->getPasswordValidators($this->getJwtService()->getAuthenticatedUser()->getAuthPassword())
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
