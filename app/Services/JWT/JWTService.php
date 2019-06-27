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
     * @return UserModelInterface
     */
    public function getAuthenticatedUser(): UserModelInterface;

    /**
     * @param UserModelInterface $user
     * @param Response           $response
     * @param bool               $withRefreshToken
     *
     * @return Response
     */
    public function issueTokens(UserModelInterface $user, Response $response, bool $withRefreshToken = false): Response;

    /**
     * @param UserModelInterface $user
     * @param int|null           $ttl
     *
     * @return string
     */
    public function createJWT(UserModelInterface $user, int $ttl = null): string;

    /**
     * @param string $token
     *
     * @return string
     */
    public function verifyJWT(string $token): string;
}
