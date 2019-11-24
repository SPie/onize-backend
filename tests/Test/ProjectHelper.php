<?php

namespace Test;

use App\Models\Project\ProjectMetaDataElementDoctrineModel;
use App\Models\Project\ProjectMetaDataElementModel;
use App\Models\Project\ProjectMetaDataElementModelFactory;
use App\Models\Project\ProjectDoctrineModel;
use App\Models\Project\ProjectInviteDoctrineModel;
use App\Models\Project\ProjectInviteModel;
use App\Models\Project\ProjectInviteModelFactory;
use App\Models\Project\ProjectModel;
use App\Models\Project\ProjectModelFactory;
use App\Models\User\UserModelInterface;
use App\Repositories\Project\ProjectMetaDataElementRepository;
use App\Repositories\Project\ProjectInviteRepository;
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
     * @param ProjectModel|MockInterface $project
     * @param bool                       $hasMemberWithEmail
     * @param string                     $email
     *
     * @return $this
     */
    private function mockProjectModelHasMemberWithEmail(
        MockInterface $project,
        bool $hasMemberWithEmail,
        string $email
    ): self {
        $project
            ->shouldReceive('hasMemberWithEmail')
            ->with($email)
            ->andReturn($hasMemberWithEmail);

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
     *
     * @return $this
     */
    private function mockProjectServiceInvite(
        MockInterface $projectService,
        $projectInvite,
        string $uuid,
        string $email
    ): self {
        $projectService
            ->shouldReceive('invite')
            ->with($uuid, $email)
            ->andThrow($projectInvite);

        return $this;
    }

    /**
     * @param ProjectServiceInterface|MockInterface $projectService
     * @param ProjectModel|\Exception               $project
     * @param string                                $uuid
     *
     * @return $this
     */
    private function mockProjectServiceGetProject(MockInterface $projectService, $project, string $uuid): self
    {
        $projectService
            ->shouldReceive('getProject')
            ->with($uuid)
            ->andThrow($project);

        return $this;
    }

    /**
     * @param ProjectServiceInterface|MockInterface    $projectService
     * @param ProjectMetaDataElementModel[]|\Exception $metaDataElements
     * @param string                                   $uuid
     * @param array                                    $metaDataElementsData
     *
     * @return $this
     */
    private function mockProjectServiceCreateMetaDataElements(
        MockInterface $projectService,
        $metaDataElements,
        string $uuid,
        array $metaDataElementsData
    ): self {
        $expectation = $projectService
            ->shouldReceive('createMetaDataElements')
            ->with($uuid, $metaDataElementsData);

        if ($metaDataElements instanceof \Exception) {
            $expectation->andThrow($metaDataElements);

            return $this;
        }

        $expectation->andReturn($metaDataElements);

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

    /**
     * @return ProjectInviteModelFactory|MockInterface
     */
    private function createProjectInviteModelFactory(): ProjectInviteModelFactory
    {
        return m::spy(ProjectInviteModelFactory::class);
    }

    /**
     * @return ProjectInviteRepository|MockInterface
     */
    private function createProjectInviteRepository(): ProjectInviteRepository
    {
        return m::spy(ProjectInviteRepository::class);
    }

    /**
     * @param ProjectInviteRepository|MockInterface $projectInviteRepository
     * @param ProjectInviteModel|null               $projectInvite
     * @param string                                $email
     * @param ProjectModel                          $project
     *
     * @return $this
     */
    private function mockProjectInviteRepositoryFindByEmailAndProject(
        MockInterface $projectInviteRepository,
        ?ProjectInviteModel $projectInvite,
        string $email,
        ProjectModel $project
    ): self {
        $projectInviteRepository
            ->shouldReceive('findByEmailAndProject')
            ->with($email, $project)
            ->andReturn($projectInvite);

        return $this;
    }

    /**
     * @param int   $times
     * @param array $data
     *
     * @return ProjectInviteDoctrineModel[]|Collection
     */
    private function createProjectInvites(int $times = 1, array $data = []): Collection
    {
        return $this->createModels(ProjectInviteDoctrineModel::class, $times, $data);
    }

    /**
     * @return ProjectMetaDataElementModel|MockInterface
     */
    private function createProjectMetaDataElementModel(): ProjectMetaDataElementModel
    {
        return m::spy(ProjectMetaDataElementModel::class);
    }

    /**
     * @return string
     */
    private function getRandomFieldType(): string
    {
        $fieldTypes = [
            ProjectMetaDataElementModel::FIELD_TYPE_TEXT,
            ProjectMetaDataElementModel::FIELD_TYPE_NUMBER,
            ProjectMetaDataElementModel::FIELD_TYPE_DATE,
            ProjectMetaDataElementModel::FIELD_TYPE_EMAIL,
        ];

        return $fieldTypes[\mt_rand(0, \count($fieldTypes) - 1)];
    }

    /**
     * @return ProjectMetaDataElementModelFactory
     */
    private function createProjectMetaDataElementModelFactory(): ProjectMetaDataElementModelFactory
    {
        return m::spy(ProjectMetaDataElementModelFactory::class);
    }

    /**
     * @return ProjectMetaDataElementRepository|MockInterface
     */
    private function createProjectMetaDataElementRepository(): ProjectMetaDataElementRepository
    {
        return m::spy(ProjectMetaDataElementRepository::class);
    }

    /**
     * @param ProjectMetaDataElementRepository|MockInterface $metaDataElementRepository
     * @param ProjectMetaDataElementModel|null               $metaDataElement
     * @param string                                         $name
     * @param ProjectModel                                   $project
     *
     * @return $this
     */
    private function mockProjectMetaDataElementRepositoryFindByNameAndProject(
        MockInterface $metaDataElementRepository,
        ?ProjectMetaDataElementModel $metaDataElement,
        string $name,
        ProjectModel $project
    ): self {
        $metaDataElementRepository
            ->shouldReceive('findByNameAndProject')
            ->with($name, $project)
            ->andReturn($metaDataElement);

        return $this;
    }

    /**
     * @param int   $times
     * @param array $data
     *
     * @return ProjectMetaDataElementModel[]|Collection
     */
    private function createProjectMetaDataElements(int $times = 1, array $data = []): Collection
    {
        return $this->createModels(ProjectMetaDataElementDoctrineModel::class, $times, $data);
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
