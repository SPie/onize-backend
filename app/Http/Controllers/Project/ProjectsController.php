<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\User\UserModelInterface;
use App\Services\Project\ProjectServiceInterface;
use Illuminate\Http\JsonResponse;

/**
 * Class ProjectsController
 *
 * @package App\Http\Controllers\Project
 */
final class ProjectsController extends Controller
{
    const ROUTE_NAME_LIST = 'projects.list';

    const RESPONSE_PARAMETER_PROJECTS = 'projects';

    /**
     * @var UserModelInterface
     */
    private $authenticatedUser;

    /**
     * ProjectsController constructor.
     *
     * @param UserModelInterface $authenticatedUser
     */
    public function __construct(UserModelInterface $authenticatedUser)
    {
        $this->authenticatedUser = $authenticatedUser;
    }

    /**
     * @return UserModelInterface
     */
    private function getAuthenticatedUser(): UserModelInterface
    {
        return $this->authenticatedUser;
    }

    //region Controller actions

    /**
     * @return JsonResponse
     */
    public function projects(): JsonResponse
    {
        return $this->createResponse([self::RESPONSE_PARAMETER_PROJECTS => $this->getAuthenticatedUser()->getProjects()]);
    }

    //endregion
}
