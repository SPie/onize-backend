<?php

namespace Test;

use App\Exceptions\Project\InvalidInviteTokenException;
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
     * @param ProjectModel|MockInterface $project
     * @param int                        $id
     *
     * @return $this
     */
    private function mockProjectModelGetId(MockInterface $project, int $id): self
    {
        $project
            ->shouldReceive('getId')
            ->andReturn($id);

        return $this;
    }

    /**
     * @param ProjectModel|MockInterface               $projectModel
     * @param ProjectMetaDataElementModel[]|Collection $projectMetaDataElements
     *
     * @return $this
     */
    private function mockProjectModelGetProjectMetaDataElements(
        MockInterface $projectModel,
        Collection $projectMetaDataElements
    ): self {
        $projectModel
            ->shouldReceive('getProjectMetaDataElements')
            ->andReturn($projectMetaDataElements);

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
     * @param ProjectServiceInterface|MockInterface    $projectService
     * @param ProjectMetaDataElementModel[]|\Exception $metaDataElements
     * @param array                                    $metaDataElementsData
     *
     * @return $this
     */
    private function mockProjectServiceUpdateMetaDataElements(
        MockInterface $projectService,
        $metaDataElements,
        array $metaDataElementsData
    ): self {
        $expectation = $projectService
            ->shouldReceive('updateMetaDataElements')
            ->with($metaDataElementsData);

        if ($metaDataElements instanceof \Exception) {
            $expectation->andThrow($metaDataElements);

            return $this;
        }

        $expectation->andReturn($metaDataElements);

        return $this;
    }

    /**
     * @param ProjectServiceInterface|MockInterface $projectService
     * @param string                                $uuid
     * @param \Exception|null                       $exception
     *
     * @return $this
     */
    private function mockProjectServiceRemoveProjectMetaDataElement(
        MockInterface $projectService,
        string $uuid,
        \Exception $exception = null
    ): self {
        $expectation = $projectService
            ->shouldReceive('removeProjectMetaDataElement')
            ->with($uuid);

        if ($exception) {
            $expectation->andThrow($exception);

            return $this;
        }

        $expectation->andReturn($projectService);

        return $this;
    }

    /**
     * @param ProjectServiceInterface|MockInterface $projectService
     * @param ProjectInviteModel|\exception         $projectInviteModel
     * @param string                                $token
     * @param string                                $email
     *
     * @return $this
     */
    private function mockProjectServiceVerifyInvite(
        MockInterface $projectService,
        $projectInviteModel,
        string $token,
        string $email
    ): self {
        $projectService
            ->shouldReceive('verifyInvite')
            ->with($token, $email)
            ->andThrow($projectInviteModel);

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
     * @param ProjectInviteModel|MockInterface $projectInviteModel
     * @param ProjectModel                     $projectModel
     *
     * @return $this
     */
    private function mockProjectInviteModelGetProject(MockInterface $projectInviteModel, ProjectModel $projectModel): self
    {
        $projectInviteModel
            ->shouldReceive('getProject')
            ->andReturn($projectModel);

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
     * @param ProjectMetaDataElementModel|MockInterface $projectMetaDataElementModel
     * @param int|null                                  $id
     *
     * @return $this
     */
    private function mockProjectMetaDataElementModelGetId(
        MockInterface $projectMetaDataElementModel,
        ?int $id
    ): self {
        $projectMetaDataElementModel
            ->shouldReceive('getId')
            ->andReturn($id);

        return $this;
    }

    /**
     * @param ProjectMetaDataElementModel|MockInterface $projectMetaDataElementModel
     * @param int                                       $position
     *
     * @return $this
     */
    private function mockProjectMetaDataElementModelGetPosition(
        MockInterface $projectMetaDataElementModel,
        int $position
    ): self {
        $projectMetaDataElementModel
            ->shouldReceive('getPosition')
            ->andReturn($position);

        return $this;
    }

    /**
     * @param ProjectMetaDataElementModel|MockInterface $projectMetaDataElementModel
     * @param ProjectModel                              $project
     *
     * @return $this
     */
    private function mockProjectMetaDataElementModelGetProject(
        MockInterface $projectMetaDataElementModel,
        ProjectModel $project
    ): self {
        $projectMetaDataElementModel
            ->shouldReceive('getProject')
            ->andReturn($project);

        return $this;
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
     * @param string                                         $uuid
     *
     * @return $this
     */
    private function mockProjectMetaDataElementRepositoryFindOneByUuid(
        MockInterface $metaDataElementRepository,
        ?ProjectMetaDataElementModel $metaDataElement,
        string $uuid
    ): self {
        $metaDataElementRepository
            ->shouldReceive('findOneByUuid')
            ->with($uuid)
            ->andReturn($metaDataElement);

        return $this;
    }

    /**
     * @param ProjectMetaDataElementRepository|MockInterface $projectMetaDataElementRepository
     * @param int                                            $id
     * @param int                                            $position
     *
     * @return $this
     */
    private function mockProjectMetaDataElementRepositoryDecreasePosition(
        MockInterface $projectMetaDataElementRepository,
        int $id,
        int $position
    ): self {
        $projectMetaDataElementRepository
            ->shouldReceive('decreasePosition')
            ->with($id, $position)
            ->andReturn($projectMetaDataElementRepository);

        return $this;
    }

    /**
     * @param ProjectMetaDataElementRepository|MockInterface $projectMetaDataElementRepository
     * @param int                                            $id
     * @param int                                            $position
     *
     * @return $this
     */
    private function assertProjectMetaDataElementRepositoryDecreasePosition(
        MockInterface $projectMetaDataElementRepository,
        int $id,
        int $position
    ): self {
        $projectMetaDataElementRepository
            ->shouldHaveReceived('decreasePosition')
            ->with($id, $position)
            ->once();

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

    /**
     * @param ProjectServiceInterface|MockInterface $projectService
     * @param string                                $uuid
     *
     * @return $this
     */
    private function assertProjectServiceRemoveProjectMetaDataElement(
        MockInterface $projectService,
        string $uuid
    ): self {
        $projectService
            ->shouldHaveReceived('removeProjectMetaDataElement')
            ->with($uuid)
            ->once();

        return $this;
    }

    //endregion
}
