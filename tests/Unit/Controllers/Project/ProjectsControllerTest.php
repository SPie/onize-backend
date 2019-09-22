<?php

use App\Http\Controllers\Project\ProjectsController;
use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelInterface;
use App\Services\Project\ProjectServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Test\AuthHelper;
use Test\ModelHelper;
use Test\ProjectHelper;
use Test\RequestResponseHelper;
use Test\UserHelper;

/**
 * Class ProjectsControllerTest
 */
final class ProjectsControllerTest extends TestCase
{
    use AuthHelper;
    use ProjectHelper;
    use RequestResponseHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testProjects(): void
    {
        $project = $this->createProjectModel();
        $user = $this->createUserModel();
        $this->mockUserModelGetProjects($user, new Collection([$project]));

        $this->assertJsonResponse(
            $this->createJsonResponse($this->createJsonResponseData(['projects' => [$project]])),
            $this->getProjectsController($user)->projects()
        );
    }

    /**
     * @return void
     */
    public function testProjectsWithoutProjects(): void
    {
        $user = $this->createUserModel();
        $this->mockUserModelGetProjects($user, new Collection());

        $this->assertJsonResponse(
            $this->createJsonResponse($this->createJsonResponseData(['projects' => []])),
            $this->getProjectsController($user)->projects()
        );
    }

    /**
     * @return void
     */
    public function testAdd(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('label', $this->getFaker()->uuid);
        $request->offsetSet('description', $this->getFaker()->text);
        $user = $this->createUserModel();
        $project = $this->createProjectModel();
        $projectService = $this->createProjectService();
        $this->mockProjectServiceCreateProject(
            $projectService,
            $project,
            ['label' => $request->get('label'), 'description' => $request->get('description')],
            $user
        );

        $this->assertJsonResponse(
            $this->createJsonResponse($this->createJsonResponseData(['project' => $project]), 201),
            $this->getProjectsController($user, $projectService)->add($request)
        );
    }

    /**
     * @return void
     */
    public function testAddWithMissingLabel(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('description', $this->getFaker()->text);

        $this->expectException(ValidationException::class);

        $this->getProjectsController($this->createUserModel(), $this->createProjectService())->add($request);
    }

    /**
     * @return void
     */
    public function testAddWithMissingDescription(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('label', $this->getFaker()->uuid);
        $user = $this->createUserModel();
        $project = $this->createProjectModel();
        $projectService = $this->createProjectService();
        $this->mockProjectServiceCreateProject(
            $projectService,
            $project,
            ['label' => $request->get('label')],
            $user
        );

        $this->assertJsonResponse(
            $this->createJsonResponse($this->createJsonResponseData(['project' => $project]), 201),
            $this->getProjectsController($user, $projectService)->add($request)
        );
    }

    //endregion

    /**
     * @param UserModelInterface|null      $user
     * @param ProjectServiceInterface|null $projectService
     *
     * @return ProjectsController
     */
    private function getProjectsController(
        UserModelInterface $user = null,
        ProjectServiceInterface $projectService = null
    ): ProjectsController {
        return new ProjectsController(
            $user ?: $this->createUserModel(),
            $projectService ?: $this->createProjectService()
        );
    }
}
