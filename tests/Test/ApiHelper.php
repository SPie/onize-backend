<?php

namespace Test;

use App\Http\Middleware\ApiSignature;
use App\Models\User\RefreshTokenModel;
use App\Models\User\UserModelInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SPie\LaravelJWT\Contracts\JWT;
use SPie\LaravelJWT\Contracts\JWTHandler;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Trait ApiHelper
 *
 * @package Test
 */
trait ApiHelper
{

    /**
     * @param string      $uri
     * @param string      $method
     * @param array       $parameters
     * @param Cookie|null $authToken
     * @param array       $headers
     *
     * @return JsonResponse
     *
     * @throws \Exception
     */
    protected function doApiCall(
        string $uri,
        string $method = Request::METHOD_GET,
        array $parameters = [],
        Cookie $authToken = null,
        array $headers = []
    ): JsonResponse {
        $cookies = [];
        if (!empty($authToken)) {
            $cookies[$authToken->getName()] = $authToken->getValue();
        }

        $timestamp = (new \DateTime())->getTimestamp();

        return $this->call(
            $method,
            $uri,
            $parameters,
            $cookies,
            [],
            $this->transformHeadersToServerVars(
                \array_merge(
                    [
                        ApiSignature::HEADER_SIGNATURE => $this->createSignature($timestamp, $parameters),
                        ApiSignature::HEADER_TIMESTAMP => $timestamp,
                    ],
                    $headers
                )
            )
        );
    }

    /**
     * @param string $timestamp
     * @param array  $parameters
     *
     * @return string
     */
    protected function createSignature(string $timestamp, array $parameters): string
    {
        return \base64_encode(\hash_hmac(
            ApiSignature::ALGORITHM_SHA_512,
            $timestamp . \json_encode($parameters),
            $this->app['config']['middlewares.apiSignature.secret']
        ));
    }

    /**
     * @param UserModelInterface $user
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function createAuthHeader(UserModelInterface $user): array
    {
        return [
            $this->getTokenKey() => $this->getTokenPrefix() . ' ' . $this->createJWTToken($user)->getJWT(),
        ];
    }

    /**
     * @param JWT $jwt
     *
     * @return Cookie
     */
    protected function createRefreshTokenCookie(JWT $jwt): Cookie
    {
        return new Cookie('refresh-token', $jwt->getJWT());
    }

    /**
     * @param UserModelInterface     $user
     * @param RefreshTokenModel|null $refreshToken
     *
     * @return JWT
     *
     * @throws \Exception
     */
    protected function createJWTToken(UserModelInterface $user, RefreshTokenModel $refreshToken = null): JWT
    {
        /** @var JWTHandler $jwtService */
        $jwtService = $this->app->get(JWTHandler::class);

        $claims = $user->getCustomClaims();
        if (!empty($refreshToken)) {
            $claims = \array_merge(
                $claims,
                [
                    JWT::CUSTOM_CLAIM_REFRESH_TOKEN => $refreshToken->getIdentifier(),
                ]
            );
        }

        return $jwtService->createJWT($user->getAuthIdentifier(), $claims);
    }

    /**
     * @param JsonResponse $jsonResponse
     *
     * @return null|string
     */
    protected function getAuthorizationHeader(JsonResponse $jsonResponse): ?string
    {
        return $this->getHeaderValue($jsonResponse, $this->getTokenKey());
    }

    /**
     * @return null|string
     */
    protected function getTokenKey(): ?string
    {
        return 'Authorization';
    }

    /**
     * @return null|string
     */
    protected function getTokenPrefix(): ?string
    {
        return 'Bearer';
    }

    /**
     * @param JsonResponse $jsonResponse
     *
     * @return string|null
     */
    protected function getRefreshTokenCookie(JsonResponse $jsonResponse):? string
    {
        return $this->getCookieValue($jsonResponse, 'refresh-token');
    }
}
