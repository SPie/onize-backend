<?php

use App\Exceptions\Auth\NotAllowedException;
use App\Exceptions\InvalidParameterException;
use App\Exceptions\ModelNotFoundException;
use App\Exceptions\Project\InvalidInviteTokenException;
use App\Exceptions\Project\UserAlreadyMemberException;
use App\Models\Project\ProjectMetaDataElementModelFactory;
use App\Models\Project\ProjectInviteModel;
use App\Models\Project\ProjectInviteModelFactory;
use App\Models\Project\ProjectModel;
use App\Models\Project\ProjectModelFactory;
use App\Models\User\UserModelInterface;
use App\Repositories\Project\ProjectMetaDataElementRepository;
use App\Repositories\Project\ProjectInviteRepository;
use App\Repositories\Project\ProjectRepository;
use App\Services\Project\ProjectService;
use Illuminate\Support\Collection;
use Mockery as m;
use Mockery\MockInterface;
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
    public function testGetProject(): void
    {
        $uuid = $this->getFaker()->uuid;
        $project = $this->createProjectModel();
        $projectRepository = $this->createProjectRepository();
        $this->mockProjectRepositoryFindByUuid($projectRepository, $project, $uuid);

        $this->assertEquals(
            $project,
            $this->getProjectService($projectRepository)->getProject($uuid)
        );
    }

    /**
     * @return void
     */
    public function testGetProjectWithoutProject(): void
    {
        $uuid = $this->getFaker()->uuid;
        $projectRepository = $this->createProjectRepository();
        $this->mockProjectRepositoryFindByUuid($projectRepository, null, $uuid);

        $this->expectException(ModelNotFoundException::class);

        $this->getProjectService($projectRepository)->getProject($uuid);
    }

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

    /**
     * @return void
     */
    public function testSuccessfulInvite(): void
    {
        $uuid = $this->getFaker()->uuid;
        $email = $this->getFaker()->safeEmail;
        $project = $this->createProjectModelForInvite();
        $projectRepository = $this->createProjectRepository();
        $this->mockProjectRepositoryFindByUuid($projectRepository, $project, $uuid);
        $projectInviteRepository = $this->createProjectInviteRepository();
        $projectInviteModel = $this->createProjectInviteModel();
        $this->mockRepositorySave($projectInviteRepository, $projectInviteModel);
        $projectInviteModelFactory = $this->createProjectInviteModelFactory();
        $this->mockProjectInviteModelFactoryCreate(
            $projectInviteModelFactory,
            $projectInviteModel,
            [
                'email'   => $email,
                'project' => $project,
            ]
        );

        $this->assertEquals(
            $projectInviteModel,
            $this->getProjectService(
                $projectRepository,
                null,
                $projectInviteRepository,
                $projectInviteModelFactory
            )->invite($uuid, $email)
        );
        $this->assertRepositorySave($projectInviteRepository, $projectInviteModel);
    }

    /**
     * @return void
     */
    public function testInviteWithoutProject(): void
    {
        $uuid = $this->getFaker()->uuid;
        $projectRepository = $this->createProjectRepository();
        $this->mockProjectRepositoryFindByUuid($projectRepository, null, $uuid);

        $this->expectException(ModelNotFoundException::class);

        $this->getProjectService($projectRepository)->invite($uuid, $this->getFaker()->safeEmail);
    }

    /**
     * @return void
     */
    public function testInviteWithDuplicatedInvite(): void
    {
        $uuid = $this->getFaker()->uuid;
        $email = $this->getFaker()->safeEmail;
        $project = $this->createProjectModelForInvite();
        $projectRepository = $this->createProjectRepository();
        $this->mockProjectRepositoryFindByUuid($projectRepository, $project, $uuid);
        $initialProjectInviteModel = $this->createProjectInviteModel();
        $projectInviteModel = $this->createProjectInviteModel();
        $projectInviteRepository = $this->createProjectInviteRepository();
        $this
            ->mockRepositorySave($projectInviteRepository, $projectInviteModel)
            ->mockProjectInviteRepositoryFindByEmailAndProject(
                $projectInviteRepository,
                $initialProjectInviteModel,
                $email,
                $project
            );
        $projectInviteModelFactory = $this->createProjectInviteModelFactory();
        $this->mockProjectInviteModelFactoryFill(
            $projectInviteModelFactory,
            $projectInviteModel,
            $initialProjectInviteModel
        );

        $this->assertEquals(
            $projectInviteModel,
            $this->getProjectService(
                $projectRepository,
                null,
                $projectInviteRepository,
                $projectInviteModelFactory
            )->invite($uuid, $email)
        );
        $this->assertRepositorySave($projectInviteRepository, $projectInviteModel);
    }

    /**
     * @return void
     */
    public function testInviteWithAlreadyAcceptedInvite(): void
    {
        $uuid = $this->getFaker()->uuid;
        $email = $this->getFaker()->email;
        $project = $this->createProjectModelForInvite();
        $projectRepository = $this->createProjectRepository();
        $this
            ->mockProjectRepositoryFindByUuid($projectRepository, $project, $uuid)
            ->mockProjectModelHasMemberWithEmail($project, true, $email);

        $this->expectException(UserAlreadyMemberException::class);

        $this->getProjectService($projectRepository)->invite($uuid, $email);
    }

    /**
     * @return void
     */
    public function testInviteWithProjectOwner(): void
    {
        $uuid = $this->getFaker()->uuid;
        $email = $this->getFaker()->email;
        $user = $this->createUserModel();
        $project = $this->createProjectModelForInvite($user);
        $projectRepository = $this->createProjectRepository();
        $this
            ->mockProjectRepositoryFindByUuid($projectRepository, $project, $uuid)
            ->mockUserModelGetEmail($user, $email);

        $this->expectException(UserAlreadyMemberException::class);

        $this->getProjectService($projectRepository)->invite($uuid, $email);
    }

    /**
     * @return void
     */
    public function testVerifyInvite(): void
    {
        $token = $this->getFaker()->uuid;
        $email = $this->getFaker()->safeEmail;
        $projectInvite = $this->createProjectInviteModel();
        $projectInviteRepository = $this->createProjectInviteRepository();
        $this->mockProjectInviteRepositoryFindByTokenAndEmail($projectInviteRepository, $projectInvite, $token, $email);

        $this->assertEquals(
            $projectInvite,
            $this->getProjectService(null, null, $projectInviteRepository)
                ->verifyInvite($token, $email)
        );
    }

    /**
     * @return void
     */
    public function testVerifyInviteWithoutValidToken(): void
    {
        $token = $this->getFaker()->uuid;
        $email = $this->getFaker()->safeEmail;
        $projectInviteRepository = $this->createProjectInviteRepository();
        $this->mockProjectInviteRepositoryFindByTokenAndEmail($projectInviteRepository, null, $token, $email);

        $this->expectException(ModelNotFoundException::class);

        $this->getProjectService(null, null, $projectInviteRepository)->verifyInvite($token, $email);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataElements(): void
    {
        $uuid = $this->getFaker()->uuid;
        $metaDataElementData = [
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
        ];
        $project = $this->createProjectModel();
        $metaDataElement = $this->createProjectMetaDataElementModel();
        $projectRepository = $this->createProjectRepository();
        $metaDataElementModelFactory = $this->createProjectMetaDataElementModelFactory();
        $metaDataElementRepository = $this->createProjectMetaDataElementRepository();
        $this
            ->mockProjectRepositoryFindByUuid($projectRepository, $project, $uuid)
            ->mockModelFactoryCreate(
                $metaDataElementModelFactory,
                $metaDataElement,
                \array_merge($metaDataElementData, ['project' => $project])
            )
            ->mockRepositorySave($metaDataElementRepository, $metaDataElement, false);

        $this->assertEquals(
            [$metaDataElement],
            $this->getProjectServiceForCreateMetaDataElements(
                $projectRepository,
                $metaDataElementRepository,
                $metaDataElementModelFactory
            )->createMetaDataElements($uuid, [$metaDataElementData])
        );
        $this
            ->assertRepositorySaveWithFlush($metaDataElementRepository, $metaDataElement, false)
            ->assertRepositoryFlush($metaDataElementRepository);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataElementsWithoutProject(): void
    {
        $uuid = $this->getFaker()->uuid;
        $projectRepository = $this->createProjectRepository();
        $this->mockProjectRepositoryFindByUuid($projectRepository, null, $uuid);

        $this->expectException(ModelNotFoundException::class);

        $this->getProjectServiceForCreateMetaDataElements($projectRepository)->createMetaDataElements(
            $uuid,
            [
                [
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                ]
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateMetaDataElementsWitInvalidMetaDataElementParameter(): void
    {
        $uuid = $this->getFaker()->uuid;
        $metaDataElementData = [
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
        ];
        $project = $this->createProjectModel();
        $projectRepository = $this->createProjectRepository();
        $metaDataElementModelFactory = $this->createProjectMetaDataElementModelFactory();
        $this
            ->mockProjectRepositoryFindByUuid($projectRepository, $project, $uuid)
            ->mockModelFactoryCreate(
                $metaDataElementModelFactory,
                new InvalidParameterException(),
                \array_merge($metaDataElementData, ['project' => $project])
            );

        $this->expectException(InvalidParameterException::class);

        $this->getProjectServiceForCreateMetaDataElements(
            $projectRepository,
            null,
            $metaDataElementModelFactory
        )->createMetaDataElements($uuid, [$metaDataElementData]);
    }

    /**
     * @return void
     */
    public function testRemoveProjectMetaDataElement(): void
    {
        $uuid = $this->getFaker()->uuid;
        $projectId = $this->getFaker()->numberBetween();
        $project = $this->createProjectModel();
        $this->mockProjectModelGetId($project, $projectId);
        $position = $this->getFaker()->numberBetween();
        $projectMetaDataElement = $this->createProjectMetaDataElementModel();
        $this
            ->mockProjectMetaDataElementModelGetProject($projectMetaDataElement, $project)
            ->mockProjectMetaDataElementModelGetPosition($projectMetaDataElement, $position);
        $projectMetaDataElementRepository = $this->createProjectMetaDataElementRepository();
        $this
            ->mockProjectMetaDataElementRepositoryFindOneByUuid(
                $projectMetaDataElementRepository,
                $projectMetaDataElement,
                $uuid
            )
            ->mockProjectMetaDataElementRepositoryDecreasePosition($projectMetaDataElementRepository, $projectId, $position);

        $this->getProjectServiceForRemoveProjectMetaDataElements($projectMetaDataElementRepository)
            ->removeProjectMetaDataElement($uuid);

        $this
            ->assertProjectMetaDataElementRepositoryDecreasePosition($projectMetaDataElementRepository, $projectId, $position)
            ->assertRepositoryDelete($projectMetaDataElementRepository, $projectMetaDataElement);
    }

    /**
     * @return void
     */
    public function testRemoveProjectMetaDataElementWithoutModel(): void
    {
        $uuid = $this->getFaker()->uuid;
        $projectMetaDataElementRepository = $this->createProjectMetaDataElementRepository();
        $this->mockProjectMetaDataElementRepositoryFindOneByUuid(
            $projectMetaDataElementRepository,
            null,
            $uuid
        );

        $this->expectException(ModelNotFoundException::class);

        $this->getProjectServiceForRemoveProjectMetaDataElements($projectMetaDataElementRepository)
            ->removeProjectMetaDataElement($uuid);
    }

    /**
     * @return void
     */
    public function testUpdateMetaDataElements(): void
    {
        $metaDataElementsData = [['uuid'      => $this->getFaker()->numberBetween()]];
        $metaDataElement = $this->createProjectMetaDataElementModel();
        $projectMetaDataElementRepository = $this->createProjectMetaDataElementRepository();
        $this
            ->mockProjectMetaDataElementRepositoryFindOneByUuid(
                $projectMetaDataElementRepository,
                $metaDataElement,
                $metaDataElementsData[0]['uuid']
            )
            ->mockRepositorySave($projectMetaDataElementRepository, $metaDataElement, false);
        $projectMetaDataElementModelFactory = $this->createProjectMetaDataElementModelFactory();
        $this->mockModelFactoryFill(
            $projectMetaDataElementModelFactory,
            $metaDataElement,
            $metaDataElementsData[0],
            $metaDataElement
        );

        $this->assertEquals(
            [$metaDataElement],
            $this->getProjectService(
                null,
                null,
                null,
                null,
                $projectMetaDataElementRepository,
                $projectMetaDataElementModelFactory
            )->updateMetaDataElements($metaDataElementsData)
        );
        $this->assertRepositoryFlush($projectMetaDataElementRepository);
    }

    /**
     * @return void
     */
    public function testUpdateMetaDataElementsWithoutElement(): void
    {
        $metaDataElementsData = [['uuid'      => $this->getFaker()->numberBetween()]];
        $projectMetaDataElementRepository = $this->createProjectMetaDataElementRepository();
        $this ->mockProjectMetaDataElementRepositoryFindOneByUuid(
            $projectMetaDataElementRepository,
            null,
            $metaDataElementsData[0]['uuid']
        );

        $this->expectException(ModelNotFoundException::class);

        $this->getProjectService(
            null,
            null,
            null,
            null,
            $projectMetaDataElementRepository
        )->updateMetaDataElements($metaDataElementsData);
    }

    /**
     * @return void
     */
    public function testUpdateMetaDataElementsWithInvalidParameters(): void
    {
        $metaDataElementsData = [['uuid'      => $this->getFaker()->numberBetween()]];
        $metaDataElement = $this->createProjectMetaDataElementModel();
        $projectMetaDataElementRepository = $this->createProjectMetaDataElementRepository();
        $this ->mockProjectMetaDataElementRepositoryFindOneByUuid(
            $projectMetaDataElementRepository,
            $metaDataElement,
            $metaDataElementsData[0]['uuid']
        );
        $projectMetaDataElementModelFactory = $this->createProjectMetaDataElementModelFactory();
        $this->mockModelFactoryFill(
            $projectMetaDataElementModelFactory,
            new InvalidParameterException(),
            $metaDataElementsData[0],
            $metaDataElement
        );

        $this->expectException(InvalidParameterException::class);

        $this->getProjectService(
            null,
            null,
            null,
            null,
            $projectMetaDataElementRepository,
            $projectMetaDataElementModelFactory
        )->updateMetaDataElements($metaDataElementsData);
    }

    /**
     * @return void
     */
    public function testGetMetaDataElement(): void
    {
        $uuid = $this->getFaker()->uuid;
        $metaDataElement = $this->createProjectMetaDataElementModel();
        $projectMetaDataElementRepository = $this->createProjectMetaDataElementRepository();
        $this->mockProjectMetaDataElementRepositoryFindOneByUuid($projectMetaDataElementRepository, $metaDataElement, $uuid);

        $this->assertEquals(
            $metaDataElement,
            $this->getProjectService(
                null,
                null,
                null,
                null,
                $projectMetaDataElementRepository
            )->getMetaDataElement($uuid)
        );
    }

    /**
     * @return void
     */
    public function testGetMetaDataElementWithoutModel(): void
    {
        $uuid = $this->getFaker()->uuid;
        $projectMetaDataElementRepository = $this->createProjectMetaDataElementRepository();
        $this->mockProjectMetaDataElementRepositoryFindOneByUuid($projectMetaDataElementRepository, null, $uuid);

        $this->expectException(ModelNotFoundException::class);

        $this->getProjectService(
            null,
            null,
            null,
            null,
            $projectMetaDataElementRepository
        )->getMetaDataElement($uuid);
    }

    /**
     * @return void
     */
    public function testGetMetaDataValidatorsWithStringMetaDataElement(): void
    {
        [$projectService, $project, $metaDataElements] = $this->setupGetMetaDataValidatorsTest();

        $this->assertEquals([$metaDataElements->first()->getLabel() => ['string']], $projectService->getMetaDataValidators($project));
    }

    /**
     * @return void
     */
    public function testGetMetaDataValidatorsWithEmailDataElement(): void
    {
        [$projectService, $project, $metaDataElements] = $this->setupGetMetaDataValidatorsTest('email');

        $this->assertEquals([$metaDataElements->first()->getLabel() => ['email']], $projectService->getMetaDataValidators($project));
    }

    /**
     * @return void
     */
    public function testGetMetaDataValidatorsWithDateDataElement(): void
    {
        [$projectService, $project, $metaDataElements] = $this->setupGetMetaDataValidatorsTest('date');

        $this->assertEquals([$metaDataElements->first()->getLabel() => ['date']], $projectService->getMetaDataValidators($project));
    }

    /**
     * @return void
     */
    public function testGetMetaDataValidatorsWithNumberDataElement(): void
    {
        [$projectService, $project, $metaDataElements] = $this->setupGetMetaDataValidatorsTest('number');

        $this->assertEquals([$metaDataElements->first()->getLabel() => ['integer']], $projectService->getMetaDataValidators($project));
    }

    /**
     * @return void
     */
    public function testGetMetaDataValidatorsWithRequiredDataElement(): void
    {
        [$projectService, $project, $metaDataElements] = $this->setupGetMetaDataValidatorsTest('text', true);

        $this->assertEquals([$metaDataElements->first()->getLabel() => ['string', 'required']], $projectService->getMetaDataValidators($project));
    }

    /**
     * @return void
     */
    public function testGetMetaDataValidatorsWithMultipleMetaDataElements(): void
    {
        [$projectService, $project, $metaDataElements] = $this->setupGetMetaDataValidatorsTest('text', false, 2);

        $expectedValidators = [];
        foreach ($metaDataElements->all() as $metaDataElement) {
            $expectedValidators[$metaDataElement->getLabel()] = ['string'];
        }
        $this->assertEquals($expectedValidators, $projectService->getMetaDataValidators($project));
    }

    /**
     * @param string $type
     * @param bool $required
     * @param int $numberOfElements
     *
     * @return array
     */
    private function setupGetMetaDataValidatorsTest(
        string $type = 'text',
        bool $required = false,
        int $numberOfElements = 1
    ): array {
        $metaDataElements = Collection::times($numberOfElements, function ($i) use ($type, $required) {
            $metaDataElement = $this->createProjectMetaDataElementModel();
            $this
                ->mockProjectMetaDataElementModelGetFieldType($metaDataElement, $type)
                ->mockProjectMetaDataElementModelIsRequired($metaDataElement, $required)
                ->mockProjectMetaDataElementModelGetLabel($metaDataElement, $i);

            return $metaDataElement;
        });
        $project = $this->createProjectModel();
        $projectMetaDataElementRepository = $this->createProjectMetaDataElementRepository();
        $this->mockProjectMetaDataElementRepositoryFindByProject($projectMetaDataElementRepository, $metaDataElements, $project);
        $projectService = $this->getProjectService(
            null,
            null,
            null,
            null,
            $projectMetaDataElementRepository
        );

        return [$projectService, $project, $metaDataElements];
    }

    //endregion

    /**
     * @param ProjectRepository|null                  $projectRepository
     * @param ProjectModelFactory|null                $projectModelFactory
     * @param ProjectInviteRepository|null            $projectInviteRepository
     * @param ProjectInviteModelFactory|null          $projectInviteModelFactory
     * @param ProjectMetaDataElementRepository|null   $metaDataElementRepository
     * @param ProjectMetaDataElementModelFactory|null $metaDataElementModelFactory
     *
     * @return ProjectService
     */
    private function getProjectService(
        ProjectRepository $projectRepository = null,
        ProjectModelFactory $projectModelFactory = null,
        ProjectInviteRepository $projectInviteRepository = null,
        ProjectInviteModelFactory $projectInviteModelFactory = null,
        ProjectMetaDataElementRepository $metaDataElementRepository = null,
        ProjectMetaDataElementModelFactory $metaDataElementModelFactory = null
    ): ProjectService {
        return new ProjectService(
            $projectRepository ?: $this->createProjectRepository(),
            $projectModelFactory ?: $this->createProjectModelFactory(),
            $projectInviteRepository ?: $this->createProjectInviteRepository(),
            $projectInviteModelFactory ?: $this->createProjectInviteModelFactory(),
            $metaDataElementRepository ?: $this->createProjectMetaDataElementRepository(),
            $metaDataElementModelFactory ?: $this->createProjectMetaDataElementModelFactory()
        );
    }

    /**
     * @param ProjectRepository|null                  $projectRepository
     * @param ProjectMetaDataElementRepository|null   $metaDataElementRepository
     * @param ProjectMetaDataElementModelFactory|null $metaDataElementModelFactory
     *
     * @return ProjectService
     */
    private function getProjectServiceForCreateMetaDataElements(
        ProjectRepository $projectRepository = null,
        ProjectMetaDataElementRepository $metaDataElementRepository = null,
        ProjectMetaDataElementModelFactory $metaDataElementModelFactory = null
    ): ProjectService {
        return new ProjectService(
            $projectRepository ?: $this->createProjectRepository(),
            $this->createProjectModelFactory(),
            $this->createProjectInviteRepository(),
            $this->createProjectInviteModelFactory(),
            $metaDataElementRepository ?: $this->createProjectMetaDataElementRepository(),
            $metaDataElementModelFactory ?: $this->createProjectMetaDataElementModelFactory()
        );
    }

    /**
     * @param ProjectMetaDataElementRepository|null $metaDataElementRepository
     *
     * @return ProjectService
     */
    private function getProjectServiceForRemoveProjectMetaDataElements(
        ProjectMetaDataElementRepository $metaDataElementRepository = null
    ): ProjectService {
        return new ProjectService(
            $this->createProjectRepository(),
            $this->createProjectModelFactory(),
            $this->createProjectInviteRepository(),
            $this->createProjectInviteModelFactory(),
            $metaDataElementRepository ?: $this->createProjectMetaDataElementRepository(),
            $this->createProjectMetaDataElementModelFactory()
        );
    }

    /**
     * @param UserModelInterface|null $user
     *
     * @return ProjectModel
     */
    private function createProjectModelForInvite(UserModelInterface $user = null): ProjectModel
    {
        $project = $this->createProjectModel();
        $this->mockProjectModelGetUser($project, $user ?: $this->createUserModel());

        return $project;
    }

    /**
     * @param ProjectInviteModelFactory|MockInterface $projectInviteModelFactory
     * @param ProjectInviteModel|\Exception           $projectInviteModel
     * @param array                                   $data
     *
     * @return $this
     */
    private function mockProjectInviteModelFactoryCreate(
        MockInterface $projectInviteModelFactory,
        $projectInviteModel,
        array $data
    ): self {
        $projectInviteModelFactory
            ->shouldReceive('create')
            ->with(m::on(function (array $argument) use ($data) {
                return !empty($argument['token'])
                    && $argument['email'] == $data['email']
                    && $argument['project'] == $data['project'];
            }))
            ->andThrow($projectInviteModel);

        return $this;
    }

    /**
     * @param ProjectInviteModelFactory|MockInterface $projectInviteModelFactory
     * @param ProjectInviteModel|\Exception           $projectInviteModel
     * @param ProjectInviteModel                      $initialProjectInviteModel
     *
     * @return $this
     */
    private function mockProjectInviteModelFactoryFill(
        MockInterface $projectInviteModelFactory,
        $projectInviteModel,
        ProjectInviteModel $initialProjectInviteModel
    ): self {
        $projectInviteModelFactory
            ->shouldReceive('fill')
            ->with(
                $initialProjectInviteModel,
                m::on(function (array $argument) {
                    return !empty($argument['token']);
                })
            )
            ->andThrow($projectInviteModel);

        return $this;
    }
}
