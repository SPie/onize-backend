<?php

namespace App\Exceptions\Project;

use App\Exceptions\Api\ApiException;
use Illuminate\Http\Response;

/**
 * Class InvalidInviteTokenExceptiob
 *
 * @package App\Exceptions\Project
 */
final class InvalidInviteTokenException extends ApiException
{
    public function __construct()
    {
        parent::__construct(Response::HTTP_FORBIDDEN);
    }
}
