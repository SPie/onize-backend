<?php

namespace App\Http\Controllers\User;

use App\Exceptions\Auth\NotAuthenticatedException;
use App\Exceptions\ModelNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\User\UserModelInterface;
use App\Services\Email\EmailService;
use App\Services\JWT\JWTService;
use App\Services\User\UsersServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Class PasswordResetController
 *
 * @package App\Http\Controllers\User
 */
final class PasswordResetController extends Controller
{

    const ROUTE_NAME_START          = 'passwordReset.start';
    const ROUTE_NAME_VERIFY_TOKEN   = 'passwordReset.verifyToken';
    const ROUTE_NAME_RESET_PASSWORD = 'passwordReset.resetPassword';

    const REQUEST_PARAMETER_EMAIL            = 'email';
    const REQUEST_PARAMETER_RESET_TOKEN      = 'resetToken';
    const REQUEST_PARAMETER_PASSWORD         = 'password';
    const REQUEST_PARAMETER_PASSWORD_CONFIRM = 'passwordConfirm';

    const CONTEXT_RESET_TOKEN = 'resetToken';

    //region Controller actions

    /**
     * @param Request               $request
     * @param UsersServiceInterface $usersService
     * @param JWTService            $jwtService
     * @param EmailService          $emailService
     *
     * @return JsonResponse
     */
    public function start(
        Request $request,
        UsersServiceInterface $usersService,
        JWTService $jwtService,
        EmailService $emailService
    ): JsonResponse
    {
        $email = $this->getEmailFromRequest($request);

        try {
            $emailService->passwordResetEmail(
                $email,
                $jwtService->createJWT($usersService->getUserByEmail($email), 15)
            );
        } catch (ModelNotFoundException $e) {}

        return $this->createResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request               $request
     * @param UsersServiceInterface $usersService
     * @param JWTService            $jwtService
     *
     * @return JsonResponse
     */
    public function verifyToken(
        Request $request,
        UsersServiceInterface $usersService,
        JWTService $jwtService
    ): JsonResponse
    {
        $this->getUserFromToken($usersService, $jwtService, $this->getResetTokenFromRequest($request));
        return $this->createResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request               $request
     * @param UsersServiceInterface $usersService
     * @param JWTService            $jwtService
     *
     * @return JsonResponse
     */
    public function resetPassword(
        Request $request,
        UsersServiceInterface $usersService,
        JWTService $jwtService
    ): JsonResponse
    {
        list($resetToken, $password) = $this->getRequestParametersForPasswordReset($request);

        $usersService->editUser(
            $this->getUserFromToken($usersService, $jwtService, $resetToken),
            [UserModelInterface::PROPERTY_PASSWORD => $password]
        );

        return $this->createResponse([], Response::HTTP_NO_CONTENT);
    }

    //endregion

    /**
     * @param Request $request
     *
     * @return string
     *
     * @throws ValidationException
     */
    private function getEmailFromRequest(Request $request): string
    {
        return $this->validate(
            $request,
            [
                self::REQUEST_PARAMETER_EMAIL => [
                    'required',
                    'email'
                ]
            ]
        )[self::REQUEST_PARAMETER_EMAIL];
    }

    /**
     * @param Request $request
     *
     * @return string
     *
     * @throws ValidationException
     */
    private function getResetTokenFromRequest(Request $request): string
    {
        return $this->validate(
            $request,
            [self::REQUEST_PARAMETER_RESET_TOKEN => ['required']]
        )[self::REQUEST_PARAMETER_RESET_TOKEN];
    }

    /**
     * @param UsersServiceInterface $usersService
     * @param JWTService            $jwtService
     * @param string                $token
     *
     * @return UserModelInterface
     *
     * @throws NotAuthenticatedException
     */
    private function getUserFromToken(
        UsersServiceInterface $usersService,
        JWTService $jwtService,
        string $token
    ): UserModelInterface
    {
        try {
            return $usersService->getUserByEmail($jwtService->verifyJWT($token));
        } catch (ModelNotFoundException $e) {
            throw new NotAuthenticatedException();
        }
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getRequestParametersForPasswordReset(Request $request): array
    {
        $requestParameters = $this->validate(
            $request,
            [
                self::REQUEST_PARAMETER_RESET_TOKEN      => ['required'],
                self::REQUEST_PARAMETER_PASSWORD         => ['required', 'min:8'],
                self::REQUEST_PARAMETER_PASSWORD_CONFIRM => ['required', 'same:' . self::REQUEST_PARAMETER_PASSWORD],
            ]
        );

        return [
            $requestParameters[self::REQUEST_PARAMETER_RESET_TOKEN],
            $requestParameters[self::REQUEST_PARAMETER_PASSWORD]
        ];
    }
}