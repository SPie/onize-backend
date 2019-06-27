<?php

namespace App\Http\Controllers\User;

use App\Exceptions\Auth\NotAuthenticatedException;
use App\Exceptions\ModelNotFoundException;
use App\Http\Controllers\Controller;
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

    const ROUTE_NAME_START        = 'passwordReset.start';
    const ROUTE_NAME_VERIFY_TOKEN = 'passwordReset.verifyToken';

    const REQUEST_PARAMETER_EMAIL = 'email';
    const REQUEST_PARAMETER_RESET_TOKEN = 'resetToken';

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
            $emailService->queueEmail(
                EmailService::IDENTIFIER_PASSWORD_RESET,
                $email,
                [self::CONTEXT_RESET_TOKEN => $jwtService->createJWT($usersService->getUserByEmail($email), 15)]
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
        return $this
            ->validateUserEmail($usersService, $jwtService->verifyJWT($this->getResetTokenFromRequest($request)))
            ->createResponse([], Response::HTTP_NO_CONTENT);
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
     * @param string                $email
     *
     * @return PasswordResetController
     */
    private function validateUserEmail(UsersServiceInterface $usersService, string $email): PasswordResetController
    {
        try {
            $usersService->getUserByEmail($email);
        } catch (ModelNotFoundException $e) {
            throw new NotAuthenticatedException();
        }

        return $this;
    }
}