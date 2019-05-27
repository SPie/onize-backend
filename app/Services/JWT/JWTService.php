<?php

namespace App\Services\JWT;

use App\Models\User\UserModelInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface JWTService
 *
 * @package App\Services\JWT
 */
interface JWTService
{

    /**
     * @param Response $response
     * @param array    $credentials
     * @param bool     $withRefreshToken
     *
     * @return Response
     */
    public function login(Response $response, array $credentials, bool $withRefreshToken = false): Response;

    /**
     * @param Response $response
     *
     * @return Response
     */
    public function logout(Response $response): Response;

    /**
     * @param Response $response
     *
     * @return Response
     */
    public function refreshAccessToken(Response $response): Response;

    /**
     * @return UserModelInterface|null
     */
    public function getAuthenticatedUser(): ?UserModelInterface;

    /**
     * @param UserModelInterface $user
     * @param Response           $response
     * @param bool               $withRefreshToken
     *
     * @return Response
     */
    public function issueTokens(UserModelInterface $user, Response $response, bool $withRefreshToken = false): Response;
}
