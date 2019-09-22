<?php

namespace Test;

use App\Models\Project\ProjectDoctrineModel;
use App\Models\Project\ProjectModel;
use App\Models\Project\ProjectModelFactory;
use App\Models\User\UserModelInterface;
use App\Repositories\Project\ProjectRepository;
use App\Services\Project\ProjectServiceInterface;
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
     * @return ProjectRepository|MockInterface
     */
    private function createProjectRepository(): ProjectRepository
    {
        return m::spy(ProjectRepository::class);
    }

    /**
     * @return ProjectModelFactory|MockInterface
     */
    private function createProjectModelFactory(): ProjectModelFactory
    {
        return m::spy(ProjectModelFactory::class);
    }

    /**
     * @return ProjectServiceInterface
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
     * @param int   $times
     * @param array $data
     *
     * @return ProjectDoctrineModel[]|Collection
     */
    private function createProjects(int $times = 1, array $data = []): Collection
    {
        return $this->createModels(ProjectDoctrineModel::class, $times, $data);
    }
}
