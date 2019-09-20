<?php

use App\Http\Controllers\Project\ProjectsController;
use App\Models\User\UserDoctrineModel;
use App\Models\User\UserModelInterface;
use Illuminate\Support\Collection;
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
    
    //endregion

    /**
     * @param UserModelInterface|null $user
     *
     * @return ProjectsController
     */
    private function getProjectsController(
        UserModelInterface $user = null
    ): ProjectsController {
        return new ProjectsController($user ?: $this->createUserModel());
    }
}
