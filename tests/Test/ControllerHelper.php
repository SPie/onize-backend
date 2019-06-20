<?php

namespace Test;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Trait ControllerHelper
 *
 * @package Test
 */
trait ControllerHelper
{

    /**
     * @param MockInterface    $controller
     * @param array|\Exception $validatedParameters
     * @param Request          $request
     * @param array            $rules
     *
     * @return $this
     */
    protected function mockControllerValidate(
        MockInterface $controller,
        $validatedParameters,
        Request $request,
        array $rules
    )
    {
        $expectation = $controller
            ->shouldReceive('validate')
            ->with(
                Mockery::on(function ($argument) use ($request) {
                    return $argument == $request;
                }),
                $rules
            );

        if ($validatedParameters instanceof \Exception) {
            $expectation->andThrow($validatedParameters);

            return $this;
        }

        $expectation->andReturn($validatedParameters);

        return $this;
    }

    /**
     * @return ValidationException|MockInterface
     */
    protected function createValidationException()
    {
        return Mockery::mock(ValidationException::class);
    }

    /**
     * @param MockInterface $controller
     * @param JsonResponse  $response
     * @param array|null    $data
     * @param int           $statusCode
     * @param array|null    $headers
     * @param int|null      $options
     *
     * @return $this
     */
    protected function mockControllerCreateResponse(
        MockInterface $controller,
        JsonResponse $response,
        array $data = null,
        int $statusCode = null,
        array $headers = null,
        int $options = null
    )
    {
        $arguments = [];
        if ($data !== null) {
            $arguments[] = Mockery::on(function ($argument) use ($data) {
                return $argument == $data;
            });
        }
        if ($statusCode !== null) {
            $arguments[] = $statusCode;
        }
        if ($headers !== null) {
            $arguments[] = $headers;
        }
        if ($options !== null) {
            $arguments[] = $options;
        }

        $controller
            ->shouldReceive('createResponse')
            ->withArgs($arguments)
            ->andReturn($response);

        return $this;
    }
}