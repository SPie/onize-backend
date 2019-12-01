<?php

use App\Exceptions\Auth\NotAllowedException;
use App\Exceptions\ModelNotFoundException;
use App\Http\Controllers\Project\ProjectsController;
use App\Models\Project\ProjectInviteModel;
use App\Models\Project\ProjectMetaDataElementModel;
use App\Models\Project\ProjectModel;
use App\Models\User\UserModelInterface;
use App\Services\Project\ProjectServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Test\AuthHelper;
use Test\EmailHelper;
use Test\ProjectHelper;
use Test\RequestResponseHelper;
use Test\UserHelper;

/**
 * Class ProjectsControllerTest
 */
final class ProjectsControllerTest extends TestCase
{
    use AuthHelper;
    use EmailHelper;
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

    /**
     * @return void
     */
    public function testRemove(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $user = $this->createUserModel();
        $projectService = $this->createProjectService();

        $this->assertJsonResponse(
            $this->createJsonResponse($this->createJsonResponseData(), 204),
            $this->getProjectsController($user, $projectService)->remove($request)
        );
        $this->assertProjectServiceRemoveProject($projectService, $request->get('uuid'), $user);
    }

    /**
     * @return void
     */
    public function testRemoveWithoutUuid(): void
    {
        $projectService = $this->createProjectService();

        try {
            $this->getProjectsController($this->createUserModel(), $projectService)->remove($this->createRequest());

            $this->assertTrue(false);
        } catch (ValidationException $e) {
            $this->assertTrue(true);
        }

        $projectService->shouldNotHaveReceived('removeProject');
    }

    /**
     * @return void
     */
    public function testRemoveWithInvalidAuthenticatedUser(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $user = $this->createUserModel();
        $projectService = $this->createProjectService();
        $this->mockProjectServiceRemoveProject(
            $projectService,
            new NotAllowedException(),
            $request->get('uuid'),
            $user
        );

        $this->expectException(NotAllowedException::class);

        $this->getProjectsController($user, $projectService)->remove($request);
    }

    /**
     * @return void
     */
    public function testRemoveWithoutProject(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $user = $this->createUserModel();
        $projectService = $this->createProjectService();
        $this->mockProjectServiceRemoveProject(
            $projectService,
            new ModelNotFoundException(ProjectModel::class, $request->get('uuid')),
            $request->get('uuid'),
            $user
        );

        $this->expectException(ModelNotFoundException::class);

        $this->getProjectsController($user, $projectService)->remove($request);
    }

    /**
     * @return void
     */
    public function testInvite(): void
    {
        $tokenPlaceHolder = $this->getFaker()->uuid;
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet('email', $this->getFaker()->safeEmail);
        $request->offsetSet('inviteUrl', $this->getFaker()->url . '?' . $tokenPlaceHolder);
        $emailService = $this->createEmailService();
        $token = $this->getFaker()->uuid;
        $projectInvite = $this->createProjectInviteModel();
        $this->mockProjectInviteModelGetToken($projectInvite, $token);
        $projectService = $this->createProjectService();
        $this->mockProjectServiceInvite(
            $projectService,
            $projectInvite,
            $request->get('uuid'),
            $request->get('email')
        );

        $response = $this->getProjectsController(null, $projectService, $tokenPlaceHolder)->invite(
            $request,
            $emailService
        );

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEmailServiceProjectInvite(
            $emailService,
            $request->get('email'),
            \str_replace($tokenPlaceHolder, $token, $request->get('inviteUrl'))
        );
    }

    /**
     * @return void
     */
    public function testInviteWithoutProjectUuid(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('email', $this->getFaker()->safeEmail);
        $request->offsetSet('inviteUrl', $this->getFaker()->url);

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->invite($request, $this->createEmailService());
    }

    /**
     * @return void
     */
    public function testInviteWithoutEmail(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet('inviteUrl', $this->getFaker()->url);

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->invite(
            $request,
            $this->createEmailService()
        );
    }

    /**
     * @return void
     */
    public function testInviteWithInvalidEmail(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet('email', $this->getFaker()->word);
        $request->offsetSet('inviteUrl', $this->getFaker()->url);

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->invite($request, $this->createEmailService());
    }

    /**
     * @return void
     */
    public function testInviteWithoutInviteUrl(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet('email', $this->getFaker()->safeEmail);

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->invite($request, $this->createEmailService());
    }

    /**
     * @return void
     */
    public function testInviteWithInvalidUuid(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet('email', $this->getFaker()->safeEmail);
        $request->offsetSet('inviteUrl', $this->getFaker()->url);
        $projectService = $this->createProjectService();
        $this->mockProjectServiceInvite(
            $projectService,
            new ModelNotFoundException(ProjectInviteModel::class, $request->get('uuid')),
            $request->get('uuid'),
            $request->get('email')
        );

        $this->expectException(ModelNotFoundException::class);

        $this->getProjectsController(null, $projectService)->invite($request, $this->createEmailService());
    }

    /**
     * @return void
     */
    public function testDetails(): void
    {
        $uuid = $this->getFaker()->uuid;
        $projectModel = $this->createProjectModel();
        $projectService = $this->createProjectService();
        $this->mockProjectServiceGetProject($projectService, $projectModel, $uuid);

        $this->assertEquals(
            $this->createJsonResponse($this->createJsonResponseData(['project' => $projectModel])),
            $this->getProjectsController(null, $projectService)->details($uuid)
        );
    }

    /**
     * @return void
     */
    public function testDetailsWithoutProject(): void
    {
        $uuid = $this->getFaker()->uuid;
        $projectService = $this->createProjectService();
        $this->mockProjectServiceGetProject(
            $projectService,
            new ModelNotFoundException(ProjectModel::class, $uuid),
            $uuid
        );

        $this->expectException(ModelNotFoundException::class);

        $this->getProjectsController(null, $projectService)->details($uuid);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataElements(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet(
            'metaDataElements',
            [
                [
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]
            ]
        );
        $metaDataElement = $this->createProjectMetaDataElementModel();
        $projectService = $this->createProjectService();
        $this->mockProjectServiceCreateMetaDataElements(
            $projectService,
            [$metaDataElement],
            $request->get('uuid'),
            $request->get('metaDataElements')
        );

        $this->assertEquals(
            $this->createJsonResponse($this->createJsonResponseData(['metaDataElements' => [$metaDataElement]]), 201),
            $this->getProjectsController(null, $projectService)->createMetaDataElements($request)
        );
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithoutUuid(): void
    {
        $request = $this->createRequest();
        $request->offsetSet(
            'metaDataElements',
            [
                [
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]
            ]
        );

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithoutMetaDataElements(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidMetaDataElements(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('metaDataElements', $this->getFaker()->word);

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidMetaDataWithoutRequired(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet(
            'metaDataElements',
            [
                [
                    'label'    => $this->getFaker()->word,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]
            ]
        );

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidMetaDataWithInvalidRequired(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet(
            'metaDataElements',
            [
                [
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->word,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]
            ]
        );

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidMetaDataWithoutInList(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet(
            'metaDataElements',
            [
                [
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]
            ]
        );

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidMetaDataWithInvalidInList(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet(
            'metaDataElements',
            [
                [
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->word,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]
            ]
        );

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidMetaDataWithoutPosition(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet(
            'metaDataElements',
            [
                [
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->boolean,
                    'fieldType' => $this->getRandomFieldType(),
                ],
            ]
        );

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidMetaDataWithInvalidPosition(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet(
            'metaDataElements',
            [
                [
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->word,
                    'fieldType' => $this->getRandomFieldType(),
                ]
            ]
        );

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataElementsWithoutProject(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet(
            'metaDataElements',
            [
                [
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]
            ]
        );
        $projectService = $this->createProjectService();
        $this->mockProjectServiceCreateMetaDataElements(
            $projectService,
            new ModelNotFoundException(ProjectModel::class, $request->get('uuid')),
            $request->get('uuid'),
            $request->get('metaDataElements')
        );

        $this->expectException(ModelNotFoundException::class);

        $this->getProjectsController(null, $projectService)->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithoutLabel(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet(
            'metaDataElements',
            [
                [
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]
            ]
        );

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidLabel(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet(
            'metaDataElements',
            [
                [
                    'label'    => $this->getFaker()->numberBetween(),
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]
            ]
        );

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithoutFieldType(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet(
            'metaDataElements',
            [
                [
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                ]
            ]
        );

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidFieldType(): void
    {
        $request = $this->createRequest();
        $request->offsetSet('uuid', $this->getFaker()->uuid);
        $request->offsetSet(
            'metaDataElements',
            [
                [
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getFaker()->uuid,
                ]
            ]
        );

        $this->expectException(ValidationException::class);

        $this->getProjectsController()->createMetaDataElements($request);
    }

    /**
     * @return void
     */
    public function testDeleteProjectMetaDataElement(): void
    {
        $uuid = $this->getFaker()->uuid;
        $request = $this->createRequest();
        $request->offsetSet('uuid', $uuid);
        $projectService = $this->createProjectService();

        $this->assertEquals(
            $this->createJsonResponse($this->createJsonResponseData([]), 204),
            $this->getProjectsController(null, $projectService)->removeProjectMetaDataElement($request)
        );

        $this->assertProjectServiceRemoveProjectMetaDataElement($projectService, $uuid);
    }

    /**
     * @return void
     */
    public function testDeleteProjectMetaDataElementWithoutUuid(): void
    {
        $projectService = $this->createProjectService();

        $this->expectException(ValidationException::class);

        $this->getProjectsController(null, $projectService)->removeProjectMetaDataElement($this->createRequest());
    }

    /**
     * @return void
     */
    public function testDeleteProjectMetaDataElementWithoutExistingProjectMetaDataElement(): void
    {
        $uuid = $this->getFaker()->uuid;
        $request = $this->createRequest();
        $request->offsetSet('uuid', $uuid);
        $projectService = $this->createProjectService();
        $this->mockProjectServiceRemoveProjectMetaDataElement(
            $projectService,
            $uuid,
            new ModelNotFoundException(ProjectMetaDataElementModel::class, $uuid)
        );

        $this->expectException(ModelNotFoundException::class);

        $this->getProjectsController(null, $projectService)->removeProjectMetaDataElement($request);
    }

    //endregion

    /**
     * @param UserModelInterface|null      $user
     * @param ProjectServiceInterface|null $projectService
     * @param string|null                  $tokenPlaceHolder
     *
     * @return ProjectsController
     */
    private function getProjectsController(
        UserModelInterface $user = null,
        ProjectServiceInterface $projectService = null,
        string $tokenPlaceHolder = null
    ): ProjectsController {
        return new ProjectsController(
            $user ?: $this->createUserModel(),
            $projectService ?: $this->createProjectService(),
            $tokenPlaceHolder ?: $this->getFaker()->uuid
        );
    }
}
