<?php

namespace Test;

use App\Http\Response\JsonResponseData;
use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait RequestResponseHelper
 *
 * @package Test
 */
trait RequestResponseHelper
{

    /**
     * @param JsonResponse $response
     * @param string       $headerName
     *
     * @return null|string
     */
    protected function getHeaderValue(JsonResponse $response, string $headerName): ?string
    {
        return $response->headers->get($headerName);
    }

    /**
     * @param JsonResponse $response
     * @param string       $cookieName
     *
     * @return null|string
     */
    protected function getCookieValue(JsonResponse $response, string $cookieName): ?string
    {
        /** @var Cookie[] $cookies */
        $cookies = $response->headers->getCookies();
        foreach ($cookies as $cookie) {
            if ($cookie->getName() == $cookieName) {
                return $cookie->getValue();
            }
        }

        return null;
    }

    /**
     * @return Request
     */
    protected function createRequest(): Request
    {
        return new Request();
    }

    /**
     * @param string $content
     * @param int    $statusCode
     *
     * @return Response
     */
    protected function createResponse(string $content = '', int $statusCode = 200): Response
    {
        return new Response($content, $statusCode);
    }

    /**
     * @param mixed $content
     * @param int   $statusCode
     *
     * @return JsonResponse
     */
    protected function createJsonResponse($content = '', int $statusCode = 200): JsonResponse
    {
        return new JsonResponse($content, $statusCode);
    }

    /**
     * @param array $data
     *
     * @return JsonResponseData
     */
    protected function createJsonResponseData(array $data = []): JsonResponseData
    {
        return new JsonResponseData($data);
    }

    //region Assertions

    /**
     * @param JsonResponse $expected
     * @param JsonResponse $actual
     *
     * @return $this
     */
    protected function assertJsonResponse(JsonResponse $expected, JsonResponse $actual)
    {
        $this->assertEquals($expected->getData(), $actual->getData());
        $this->assertEquals($expected->getStatusCode(), $actual->getStatusCode());

        return $this;
    }

    //endregion
}