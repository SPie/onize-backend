<?php

use App\Exceptions\Auth\NotAllowedException;
use App\Exceptions\InvalidParameterException;
use App\Exceptions\ModelNotFoundException;
use App\Models\Project\ProjectModelFactory;
use App\Repositories\Project\ProjectRepository;
use App\Services\Project\ProjectService;
use Test\ModelHelper;
use Test\ProjectHelper;
use Test\RepositoryHelper;
use Test\UserHelper;

/**
 * Class ProjectServiceTest
 */
final class ProjectServiceTest extends TestCase
{
    use ModelHelper;
    use ProjectHelper;
    use RepositoryHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testCreateProject(): void
    {
        $user = $this->createUserModel();
        $projectData = [$this->getFaker()->uuid => $this->getFaker()->uuid];
        $project = $this->createProjectModel();
        $projectModelFactory = $this->createProjectModelFactory();
        $this->mockModelFactoryCreate(
            $projectModelFactory,
            $project,
            \array_merge(
                $projectData,
                ['user' => $user]
            )
        );
        $projectRepository = $this->createProjectRepository();
        $this->mockRepositorySave($projectRepository, $project);

        $this->assertEquals(
            $project,
            $this->getProjectService($projectRepository, $projectModelFactory)->createProject($projectData, $user)
        );
    }

    /**
     * @return void
     */
    public function testCreateProjectWithInvalidParameterException(): void
    {
        $user = $this->createUserModel();
        $projectData = [$this->getFaker()->uuid => $this->getFaker()->uuid];
        $projectModelFactory = $this->createProjectModelFactory();
        $this->mockModelFactoryCreate(
            $projectModelFactory,
            new InvalidParameterException(),
            \array_merge(
                $projectData,
                ['user' => $user]
            )
        );
        $projectRepository = $this->createProjectRepository();

        try {
            $this->getProjectService($projectRepository, $projectModelFactory)->createProject($projectData, $user);

            $this->assertTrue(false);
        } catch (InvalidParameterException $e) {
            $this->assertTrue(true);
        }

        $projectRepository->shouldNotHaveReceived('save');
    }

    /**
     * @return void
     */
    public function testRemoveProject(): void
    {
        $authenticatedUser = $this->createUserModel();
        $this->mockUserModelGetId($authenticatedUser, $this->getFaker()->numberBetween());
        $uuid = $this->getFaker()->uuid;
        $project = $this->createProjectModel();
        $this->mockProjectModelGetUser($project, $authenticatedUser);
        $projectRepository = $this->createProjectRepository();
        $this->mockProjectRepositoryFindByUuid($projectRepository, $project, $uuid);
        $projectService = $this->getProjectService($projectRepository);

        $this->assertEquals($projectService, $projectService->removeProject($uuid, $authenticatedUser));

        $this->assertRepositoryDelete($projectRepository, $project);
    }

    /**
     * @return void
     */
    public function testRemoveProjectWithoutProject(): void
    {
        $uuid = $this->getFaker()->uuid;
        $projectRepository = $this->createProjectRepository();
        $this->mockProjectRepositoryFindByUuid($projectRepository, null, $uuid);
        $projectService = $this->getProjectService($projectRepository);

        try {
            $projectService->removeProject($uuid, $this->createUserModel());

            $this->assertTrue(false);
        } catch (ModelNotFoundException $e) {
            $this->assertTrue(true);
        }

        $projectRepository->shouldNotHaveReceived('delete');
    }

    /**
     * @return void
     */
    public function testRemoveProjectWithInvalidAuthenticatedUser(): void
    {
        $authenticatedUser = $this->createUserModel();
        $this->mockUserModelGetId($authenticatedUser, $this->getFaker()->numberBetween());
        $uuid = $this->getFaker()->uuid;
        $project = $this->createProjectModel();
        $this->mockProjectModelGetUser($project, $this->createUserModel());
        $projectRepository = $this->createProjectRepository();
        $this->mockProjectRepositoryFindByUuid($projectRepository, $project, $uuid);
        $projectService = $this->getProjectService($projectRepository);

        $this->expectException(NotAllowedException::class);

        $projectService->removeProject($uuid, $authenticatedUser);
    }

    //endregion

    /**
     * @param ProjectRepository|null   $projectRepository
     * @param ProjectModelFactory|null $projectModelFactory
     *
     * @return ProjectService
     */
    private function getProjectService(
        ProjectRepository $projectRepository = null,
        ProjectModelFactory $projectModelFactory = null
    ): ProjectService {
        return new ProjectService(
            $projectRepository ?: $this->createProjectRepository(),
            $projectModelFactory ?: $this->createProjectModelFactory()
        );
    }
}