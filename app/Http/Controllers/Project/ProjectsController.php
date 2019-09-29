<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project\ProjectModel;
use App\Models\User\UserModelInterface;
use App\Services\Project\ProjectServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Class ProjectsController
 *
 * @package App\Http\Controllers\Project
 */
final class ProjectsController extends Controller
{
    const ROUTE_NAME_LIST = 'projects.list';
    const ROUTE_NAME_ADD  = 'projects.add';
    const ROUTE_NAME_REMOVE = 'projects.remove';

    const RESPONSE_PARAMETER_PROJECT  = 'project';
    const RESPONSE_PARAMETER_PROJECTS = 'projects';

    /**
     * @var UserModelInterface
     */
    private $authenticatedUser;

    /**
     * @var ProjectServiceInterface
     */
    private $projectService;

    /**
     * ProjectsController constructor.
     *
     * @param UserModelInterface      $authenticatedUser
     * @param ProjectServiceInterface $projectService
     */
    public function __construct(UserModelInterface $authenticatedUser, ProjectServiceInterface $projectService)
    {
        $this->authenticatedUser = $authenticatedUser;
        $this->projectService = $projectService;
    }

    /**
     * @return UserModelInterface
     */
    private function getAuthenticatedUser(): UserModelInterface
    {
        return $this->authenticatedUser;
    }

    /**
     * @return ProjectServiceInterface
     */
    private function getProjectService(): ProjectServiceInterface
    {
        return $this->projectService;
    }

    //region Controller actions

    /**
     * @return JsonResponse
     */
    public function projects(): JsonResponse
    {
        return $this->createResponse([self::RESPONSE_PARAMETER_PROJECTS => $this->getAuthenticatedUser()->getProjects()]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function add(Request $request): JsonResponse
    {
        return $this->createResponse(
            [
                self::RESPONSE_PARAMETER_PROJECT => $this->getProjectService()->createProject(
                    $this->validateProjectDataFromRequest($request),
                    $this->getAuthenticatedUser()
                )
            ],
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function remove(Request $request): JsonResponse
    {
        $this->getProjectService()->removeProject($this->validateUuidFromRequest($request), $this->getAuthenticatedUser());

        return $this->createResponse([], Response::HTTP_NO_CONTENT);
    }

    //endregion

    /**
     * @param Request $request
     *
     * @return array
     *
     * @throws ValidationException
     */
    private function validateProjectDataFromRequest(Request $request): array
    {
        return $this->validate(
            $request,
            [
                ProjectModel::PROPERTY_LABEL       => ['required'],
                ProjectModel::PROPERTY_DESCRIPTION => [],
            ]

        );
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function validateUuidFromRequest(Request $request): string
    {
        return $this->validate($request, [ProjectModel::PROPERTY_UUID => ['required']])[ProjectModel::PROPERTY_UUID];
    }
}
