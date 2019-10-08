<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\Controller;
use App\Models\Project\ProjectModel;
use App\Models\User\UserModelInterface;
use App\Services\Email\EmailService;
use App\Services\Project\ProjectServiceInterface;
use App\Services\User\UsersServiceInterface;
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

    const REQUEST_PARAMETER_EMAIL      = 'email';
    const REQUEST_PARAMETER_INVITE_URL = 'inviteUrl';

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

    private $tokenPlaceholder;

    /**
     * ProjectsController constructor.
     *
     * @param UserModelInterface      $authenticatedUser
     * @param ProjectServiceInterface $projectService
     * @param string                  $tokenPlaceholder
     */
    public function __construct(
        UserModelInterface $authenticatedUser,
        ProjectServiceInterface $projectService,
        string $tokenPlaceholder
    ) {
        $this->authenticatedUser = $authenticatedUser;
        $this->projectService = $projectService;
        $this->tokenPlaceholder = $tokenPlaceholder;
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

    /**
     * @return string
     */
    private function getTokenPlaceholder(): string
    {
        return $this->tokenPlaceholder;
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

    /**
     * @param Request               $request
     * @param UsersServiceInterface $usersService
     * @param EmailService          $emailService
     *
     * @return JsonResponse
     */
    public function invite(Request $request, UsersServiceInterface $usersService, EmailService $emailService): JsonResponse
    {
        $parameters = $this->validateDataForInvite($request);

        $projectInvite = $this->getProjectService()->invite(
            $parameters[ProjectModel::PROPERTY_UUID],
            $parameters[self::REQUEST_PARAMETER_EMAIL],
            $usersService
        );

        $emailService->projectInvite(
            $parameters[self::REQUEST_PARAMETER_EMAIL],
            $this->parseInviteUrl($parameters[self::REQUEST_PARAMETER_INVITE_URL], $projectInvite->getToken())
        );

        return $this->createResponse([], Response::HTTP_CREATED);
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

    /**
     * @param Request $request
     *
     * @return array
     */
    private function validateDataForInvite(Request $request): array
    {
        return $this->validate(
            $request,
            [
                'uuid' => ['required'],
                'email' => [
                    'required',
                    'email',
                ],
                'inviteUrl' => ['required'],
            ]
        );
    }

    /**
     * @param string $inviteUrl
     * @param string $token
     *
     * @return string
     */
    private function parseInviteUrl(string $inviteUrl, string $token): string
    {
        return \str_replace($this->getTokenPlaceholder(), $token, $inviteUrl);
    }
}
