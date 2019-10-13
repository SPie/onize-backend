<?php

namespace App\Exceptions\Project;

use App\Exceptions\Api\ApiException;
use Illuminate\Http\Response;

/**
 * Class UserAlreadyMemberException
 *
 * @package App\Exceptions\Project
 */
final class UserAlreadyMemberException extends ApiException
{
    /**
     * UserAlreadyMemberException constructor.
     *
     * @param string $message
     */
    public function __construct(string $message = '')
    {
        parent::__construct(Response::HTTP_CONFLICT, [], $message);
    }
}
