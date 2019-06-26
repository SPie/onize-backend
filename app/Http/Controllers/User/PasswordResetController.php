<?php

namespace App\Http\Controllers\User;

use App\Exceptions\ModelNotFoundException;
use App\Http\Controllers\Controller;
use App\Services\Email\EmailService;
use App\Services\JWT\JWTService;
use App\Services\User\UsersServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class PasswordResetController
 *
 * @package App\Http\Controllers\User
 */
final class PasswordResetController extends Controller
{

    const ROUTE_NAME_PASSWORD_RESET_START = 'passwordReset.start';

    const REQUEST_PARAMETER_EMAIL = 'email';

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
                'password-reset',
                $email,
                ['resetToken' => $jwtService->createJWT($usersService->getUserByEmail($email), 15)]
            );
        } catch (ModelNotFoundException $e) {}

        return $this->createResponse([], Response::HTTP_NO_CONTENT);
    }

    //endregion

    /**
     * @param Request $request
     *
     * @return string
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
}