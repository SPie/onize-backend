<?php

namespace Test;

use App\Models\Project\ProjectDoctrineModel;
use App\Models\Project\ProjectInviteModel;
use App\Models\Project\ProjectModel;
use App\Models\Project\ProjectModelFactory;
use App\Models\User\UserModelInterface;
use App\Repositories\Project\ProjectRepository;
use App\Services\Project\ProjectServiceInterface;
use App\Services\User\UsersServiceInterface;
use Illuminate\Support\Collection;
use Mockery as m;
use Mockery\MockInterface;

/**
 * Trait ProjectHelper
 *
 * @package Test
 */
trait ProjectHelper
{
    /**
     * @return ProjectModel|MockInterface
     */
    private function createProjectModel(): ProjectModel
    {
        return m::spy(ProjectModel::class);
    }

    /**
     * @param ProjectModel|MockInterface $project
     * @param UserModelInterface         $user
     *
     * @return $this
     */
    private function mockProjectModelGetUser(MockInterface $project, UserModelInterface $user): self
    {
        $project
            ->shouldReceive('getUser')
            ->andReturn($user);

        return $this;
    }

    /**
     * @return ProjectRepository|MockInterface
     */
    private function createProjectRepository(): ProjectRepository
    {
        return m::spy(ProjectRepository::class);
    }

    /**
     * @param ProjectRepository|MockInterface $projectRepository
     * @param ProjectModel|null               $project
     * @param string                          $uuid
     *
     * @return $this
     */
    private function mockProjectRepositoryFindByUuid(
        MockInterface $projectRepository,
        ?ProjectModel $project,
        string $uuid
    ): self {
        $projectRepository
            ->shouldReceive('findByUuid')
            ->with($uuid)
            ->andReturn($project);

        return $this;
    }

    /**
     * @return ProjectModelFactory|MockInterface
     */
    private function createProjectModelFactory(): ProjectModelFactory
    {
        return m::spy(ProjectModelFactory::class);
    }

    /**
     * @return ProjectServiceInterface|MockInterface
     */
    private function createProjectService(): ProjectServiceInterface
    {
        return m::spy(ProjectServiceInterface::class);
    }

    /**
     * @param ProjectServiceInterface|MockInterface $projectService
     * @param ProjectModel|\Exception               $project
     * @param array                                 $projectData
     * @param UserModelInterface                    $user
     *
     * @return $this
     */
    private function mockProjectServiceCreateProject(
        MockInterface $projectService,
        $project,
        array $projectData,
        UserModelInterface $user
    ) {
        $projectService
            ->shouldReceive('createProject')
            ->with($projectData, $user)
            ->andThrow($project);

        return $this;
    }

    /**
     * @param ProjectServiceInterface|MockInterface $projectService
     * @param ProjectModel|\Exception               $project
     * @param string                                $uuid
     * @param UserModelInterface                    $authenticatedUser
     *
     * @return $this
     */
    private function mockProjectServiceRemoveProject(
        MockInterface $projectService,
        $project,
        string $uuid,
        UserModelInterface $authenticatedUser
    ): self {
        $projectService
            ->shouldReceive('removeProject')
            ->with($uuid, $authenticatedUser)
            ->andThrow($project);

        return $this;
    }

    /**
     * @param ProjectServiceInterface|MockInterface $projectService
     * @param ProjectInviteModel|\Exception         $projectInvite
     * @param string                                $uuid
     * @param string                                $email
     * @param UsersServiceInterface                 $usersService
     *
     * @return $this
     */
    private function mockProjectServiceInvite(
        MockInterface $projectService,
        $projectInvite,
        string $uuid,
        string $email,
        UsersServiceInterface $usersService
    ): self {
        $projectService
            ->shouldReceive('invite')
            ->with($uuid, $email, $usersService)
            ->andThrow($projectInvite);

        return $this;
    }

    /**
     * @param int   $times
     * @param array $data
     *
     * @return ProjectDoctrineModel[]|Collection
     */
    private function createProjects(int $times = 1, array $data = []): Collection
    {
        return $this->createModels(ProjectDoctrineModel::class, $times, $data);
    }

    /**
     * @return ProjectInviteModel|MockInterface
     */
    private function createProjectInviteModel()
    {
        return m::spy(ProjectInviteModel::class);
    }

    /**
     * @param MockInterface $projectInviteModel
     * @param string        $token
     *
     * @return $this
     */
    private function mockProjectInviteModelGetToken(MockInterface $projectInviteModel, string $token): self
    {
        $projectInviteModel
            ->shouldReceive('getToken')
            ->andReturn($token);

        return $this;
    }

    //region Assertions

    /**
     * @param ProjectServiceInterface|MockInterface $projectService
     * @param string                                $uuid
     * @param UserModelInterface                    $user
     *
     * @return $this
     */
    private function assertProjectServiceRemoveProject(
        MockInterface $projectService,
        string $uuid,
        UserModelInterface $user
    ): self {
        $projectService
            ->shouldHaveReceived('removeProject')
            ->with($uuid, $user)
            ->once();

        return $this;
    }

    //endregion
}
