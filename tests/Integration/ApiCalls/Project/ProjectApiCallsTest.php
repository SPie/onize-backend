<?php

use App\Models\Project\ProjectMetaDataElementModel;
use App\Models\Project\ProjectModel;
use App\Repositories\Project\ProjectMetaDataElementRepository;
use App\Repositories\Project\ProjectInviteRepository;
use App\Repositories\Project\ProjectRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use LaravelDoctrine\Migrations\Testing\DatabaseMigrations;
use Test\ApiHelper;
use Test\ModelHelper;
use Test\ProjectHelper;
use Test\UserHelper;

/**
 * Class ProjectApiCallsTest
 */
final class ProjectApiCallsTest extends IntegrationTestCase
{
    use ApiHelper;
    use DatabaseMigrations;
    use ModelHelper;
    use ProjectHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testListProjects(): void
    {
        $user = $this->createUsers()->first();
        $projects = $this->createProjects(3, ['user' => $user]);
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.list'),
            Request::METHOD_GET,
            [],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseOk();
        $responseData = $response->getData(true);
        $this->assertEquals(
            $projects->map(function (ProjectModel $project) {
                return $project->toArray();
            })
            ->all(),
            $responseData['projects']
        );
    }

    /**
     * @return void
     */
    public function testListProjectsWithoutProjects(): void
    {
        $response = $this->doApiCall(
            URL::route('projects.list'),
            Request::METHOD_GET,
            [],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseOk();
        $this->assertEmpty($response->getData(true)['projects']);
    }

    /**
     * @return void
     */
    public function testAddProject(): void
    {
        $label = $this->getFaker()->uuid;
        $description = $this->getFaker()->text;

        $response = $this->doApiCall(
            URL::route('projects.add'),
            Request::METHOD_POST,
            [
                'label'       => $label,
                'description' => $description,
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(201);
        $responseData = $response->getData(true);
        $this->assertEquals($label, $responseData['project']['label']);
        $this->assertEquals($description, $responseData['project']['description']);
        $this->assertNotEmpty($this->getProjectRepository()->findBy(['uuid' => $responseData['project']['uuid']]));
    }

    /**
     * @return void
     */
    public function testAddProjectWithoutDescription(): void
    {
        $label = $this->getFaker()->uuid;

        $response = $this->doApiCall(
            URL::route('projects.add'),
            Request::METHOD_POST,
            [
                'label' => $label,
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(201);
        $responseData = $response->getData(true);
        $this->assertEquals($label, $responseData['project']['label']);
        $this->assertNotEmpty($this->getProjectRepository()->findBy(['uuid' => $responseData['project']['uuid']]));
    }

    /**
     * @return void
     */
    public function testAddProjectWithoutLabel(): void
    {
        $response = $this->doApiCall(
            URL::route('projects.add'),
            Request::METHOD_POST,
            [],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', $responseData['label'][0]);
        $this->assertEmpty($this->getProjectRepository()->findAll());
    }

    /**
     * @return void
     */
    public function testRemoveProject(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $this->doApiCall(
            URL::route('projects.remove'),
            Request::METHOD_DELETE,
            ['uuid' => $project->getUuid()],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(204);
        $this->assertEmpty($this->getProjectRepository()->findByUuid($project->getUuid()));
        $this->assertNotEmpty($this->getUserRepository()->findOneByEmail($user->getEmail()));
    }

    /**
     * @return void
     */
    public function testRemoveProjectWithMissingUuid(): void
    {
        $response = $this->doApiCall(
            URL::route('projects.remove'),
            Request::METHOD_DELETE,
            [],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', $responseData['uuid'][0]);
    }

    /**
     * @return void
     */
    public function testRemoveProjectWithoutProject(): void
    {
        $this->doApiCall(
            URL::route('projects.remove'),
            Request::METHOD_DELETE,
            ['uuid' => $this->getFaker()->uuid],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(404);
    }

    /**
     * @return void
     */
    public function testRemoveProjectWithInvalidAuthenticatedUser(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects()->first();
        $this->clearModelCache();

        $this->doApiCall(
            URL::route('projects.remove'),
            Request::METHOD_DELETE,
            ['uuid' => $project->getUuid()],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(403);
        $this->assertNotEmpty($this->getProjectRepository()->findByUuid($project->getUuid()));
    }

    /**
     * @return void
     */
    public function testInitiateProjectInvite(): void
    {
        $users = $this->createUsers(2);
        $project = $this->createProjects(1, ['user' => $users->first()])->first();
        $this->clearModelCache();

        $this->doApiCall(
            URL::route('projects.invites'),
            Request::METHOD_POST,
            [
                'uuid'      => $project->getUuid(),
                'email'     => $users->get(1)->getEmail(),
                'inviteUrl' => $this->getFaker()->url,
            ],
            null,
            $this->createAuthHeader($users->first())
        );

        $this->assertResponseStatus(201);
        $this->assertNotEmpty($this->getProjectInviteRepository()->findByEmailAndProject(
            $users->get(1)->getEmail(),
            $project
        ));
        $this->assertQueuedEmail($users->get(1)->getEmail());
    }

    /**
     * @return void
     */
    public function testInitiateProjectInviteForNonExistingUser(): void
    {
        $user = $this->createUsers()->first();
        $email = $this->getFaker()->safeEmail;
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $this->doApiCall(
            URL::route('projects.invites'),
            Request::METHOD_POST,
            [
                'uuid'      => $project->getUuid(),
                'email'     => $email,
                'inviteUrl' => $this->getFaker()->url,
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(201);
        $this->assertNotEmpty($this->getProjectInviteRepository()->findByEmailAndProject(
            $email,
            $project
        ));
    }

    /**
     * @return void
     */
    public function testInitiateProjectInviteWithMissingParameters(): void
    {
        $user = $this->createUsers()->first();
        $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.invites'),
            Request::METHOD_POST,
            [],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', $responseData['email'][0]);
        $this->assertEquals('validation.required', $responseData['uuid'][0]);
        $this->assertEquals('validation.required', $responseData['inviteUrl'][0]);
    }

    /**
     * @return void
     */
    public function testInitiateProjectInviteWithInvalidEmail(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.invites'),
            Request::METHOD_POST,
            [
                'uuid'      => $project->getUuid(),
                'inviteUrl' => $this->getFaker()->url,
                'email'     => $this->getFaker()->word,
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.email', $responseData['email'][0]);
    }

    /**
     * @return void
     */
    public function testProjectInviteWithAlreadyInvitedUser(): void
    {
        $users = $this->createUsers(2);
        $project = $this->createProjects(1, ['user' => $users->first()])->first();
        $projectInvite = $this->createProjectInvites(1, ['email' => $users->get(1)->getEmail(), 'project' => $project])->first();
        $this->clearModelCache();

        $this->doApiCall(
            URL::route('projects.invites'),
            Request::METHOD_POST,
            [
                'uuid'      => $project->getUuid(),
                'email'     => $users->get(1)->getEmail(),
                'inviteUrl' => $this->getFaker()->url,
            ],
            null,
            $this->createAuthHeader($users->first())
        );

        $this->assertResponseStatus(201);
        $updatedProjectInvite = $this->getProjectInviteRepository()->find($projectInvite->getId());
        $this->assertNotEquals($projectInvite->getToken(), $updatedProjectInvite->getToken());
    }

    /**
     * @return void
     */
    public function testProjectInviteForMember(): void
    {
        $users = $this->createUsers(2);
        $project = $this->createProjects(1, ['user' => $users->first()])->first();
        $project->addMember($users->get(1));
        $this->getProjectRepository()->save($project);
        $this->clearModelCache();

        $this->doApiCall(
            URL::route('projects.invites'),
            Request::METHOD_POST,
            [
                'uuid'      => $project->getUuid(),
                'email'     => $users->get(1)->getEmail(),
                'inviteUrl' => $this->getFaker()->url,
            ],
            null,
            $this->createAuthHeader($users->first())
        );

        $this->assertResponseStatus(409);
        $this->assertEmpty($this->getProjectInviteRepository()->findByEmailAndProject(
            $users->get(1)->getEmail(),
            $project
        ));
    }

    /**
     * @return void
     */
    public function testProjectInviteForOwner(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $this->doApiCall(
            URL::route('projects.invites'),
            Request::METHOD_POST,
            [
                'uuid'      => $project->getUuid(),
                'email'     => $user->getEmail(),
                'inviteUrl' => $this->getFaker()->url,
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(409);
        $this->assertEmpty($this->getProjectInviteRepository()->findByEmailAndProject($user->getEmail(), $project));
    }

    /**
     * @return void
     */
    public function testProjectInviteWithoutExistingProject(): void
    {
        $users = $this->createUsers(2);

        $this->doApiCall(
            URL::route('projects.invites'),
            Request::METHOD_POST,
            [
                'uuid'      => $this->getFaker()->uuid,
                'email'     => $users->get(1)->getEmail(),
                'inviteUrl' => $this->getFaker()->url,
            ],
            null,
            $this->createAuthHeader($users->first())
        );

        $this->assertResponseStatus(404);
    }

    /**
     * @return void
     */
    public function testProjectDetails(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects()->first();

        $response = $this->doApiCall(
            URL::route('projects.details', ['uuid' => $project->getUuid()]),
            Request::METHOD_GET,
            [],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseOk();
        $responseData = $response->getData(true);
        $this->assertEquals($project->toArray(), $responseData['project']);
    }

    /**
     * @return void
     */
    public function testProjectDetailsWithoutProject(): void
    {
        $user = $this->createUsers()->first();

        $response = $this->doApiCall(
            URL::route('projects.details', ['uuid' => $this->getFaker()->uuid]),
            Request::METHOD_GET,
            [],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(404);
        $this->assertEmpty($response->getData(true));
    }

    /**
     * @return void
     */
    public function testCreateMetaData(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();
        $metaDataElement = [
            'label'    => $this->getFaker()->word,
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ];

        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'uuid'             => $project->getUuid(),
                'metaDataElements' => [$metaDataElement],
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(201);
        $responseData = $response->getData(true);
        $this->assertEquals($metaDataElement['label'], $responseData['metaDataElements'][0]['label']);
        $this->assertEquals($metaDataElement['required'], $responseData['metaDataElements'][0]['required']);
        $this->assertEquals($metaDataElement['inList'], $responseData['metaDataElements'][0]['inList']);
        $this->assertEquals($metaDataElement['position'], $responseData['metaDataElements'][0]['position']);
        $this->assertEquals($metaDataElement['fieldType'], $responseData['metaDataElements'][0]['fieldType']);
        $this->assertNotEmpty($this->getMetaDataElementsRepository()->findOneBy([
            'uuid' => $responseData['metaDataElements'][0]['uuid'],
        ]));
    }

    /**
     * @return void
     */
    public function testCreateMetaDataElementWithoutUuid(): void
    {
        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'metaDataElements' => [[
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]],
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', $responseData['uuid'][0]);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataElementWithoutMetaDataElements(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            ['uuid' => $project->getUuid()],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', $responseData['metaDataElements'][0]);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidMetaDataElements(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'uuid'             => $project->getUuid(),
                'metaDataElements' => $this->getFaker()->word,
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.array', $responseData['metaDataElements'][0]);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithoutRequired(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'uuid'             => $project->getUuid(),
                'metaDataElements' => [[
                    'label'    => $this->getFaker()->word,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]],
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', $responseData['metaDataElements.0.required'][0]);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidRequired(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'uuid'             => $project->getUuid(),
                'metaDataElements' => [[
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->word,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]],
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.boolean', $responseData['metaDataElements.0.required'][0]);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithoutInList(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'uuid'             => $project->getUuid(),
                'metaDataElements' => [[
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]],
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', $responseData['metaDataElements.0.inList'][0]);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidInList(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'uuid'             => $project->getUuid(),
                'metaDataElements' => [[
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->word,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]],
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.boolean', $responseData['metaDataElements.0.inList'][0]);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithoutPosition(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'uuid'             => $project->getUuid(),
                'metaDataElements' => [[
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->word,
                    'fieldType' => $this->getRandomFieldType(),
                ]],
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', $responseData['metaDataElements.0.position'][0]);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidPosition(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'uuid'             => $project->getUuid(),
                'metaDataElements' => [[
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->word,
                    'position' => $this->getFaker()->word,
                    'fieldType' => $this->getRandomFieldType(),
                ]],
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.integer', $responseData['metaDataElements.0.position'][0]);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithoutProject(): void
    {
        $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'uuid'             => $this->getFaker()->uuid,
                'metaDataElements' => [[
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->boolean,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]],
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(404);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithoutLabel(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'uuid'             => $project->getUuid(),
                'metaDataElements' => [[
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->word,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]],
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', $responseData['metaDataElements.0.label'][0]);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidLabel(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'uuid'             => $project->getUuid(),
                'metaDataElements' => [[
                    'label'    => $this->getFaker()->numberBetween(),
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->word,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getRandomFieldType(),
                ]],
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.string', $responseData['metaDataElements.0.label'][0]);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithoutFieldType(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'uuid'             => $project->getUuid(),
                'metaDataElements' => [[
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->word,
                    'position' => $this->getFaker()->numberBetween(),
                ]],
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', $responseData['metaDataElements.0.fieldType'][0]);
    }

    /**
     * @return void
     */
    public function testCreateMetaDataWithInvalidFieldType(): void
    {
        $user = $this->createUsers()->first();
        $project = $this->createProjects(1, ['user' => $user])->first();
        $this->clearModelCache();

        $response = $this->doApiCall(
            URL::route('projects.metaDataElements'),
            Request::METHOD_POST,
            [
                'uuid'             => $project->getUuid(),
                'metaDataElements' => [[
                    'label'    => $this->getFaker()->word,
                    'required' => $this->getFaker()->boolean,
                    'inList'   => $this->getFaker()->word,
                    'position' => $this->getFaker()->numberBetween(),
                    'fieldType' => $this->getFaker()->uuid,
                ]],
            ],
            null,
            $this->createAuthHeader($user)
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.in', $responseData['metaDataElements.0.fieldType'][0]);
    }

    /**
     * @return void
     */
    public function testRemoveProjectMetaDataElement(): void
    {
        $projectMetaDataElement = $this->createProjectMetaDataElements()->first();

        $this->doApiCall(
            URL::route('projects.removeProjectMetaDataElement'),
            Request::METHOD_DELETE,
            ['uuid' => $projectMetaDataElement->getUuid()],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(204);
        $this->assertEmpty($this->getMetaDataElementsRepository()->findOneByUuid($projectMetaDataElement->getUuid()));
    }

    /**
     * @return void
     */
    public function testRemoveProjectMetaDataElementWithoutUuid(): void
    {
        $response = $this->doApiCall(
            URL::route('projects.removeProjectMetaDataElement'),
            Request::METHOD_DELETE,
            [],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', $responseData['uuid'][0]);
    }

    /**
     * @return void
     */
    public function testRemoveProjectMetaDataElementWithoutModel(): void
    {
        $this->doApiCall(
            URL::route('projects.removeProjectMetaDataElement'),
            Request::METHOD_DELETE,
            ['uuid' => $this->getFaker()->uuid],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(404);
    }

    /**
     * @return void
     */
    public function testDecreasePositionOnRemoveProjectMetaDataElement(): void
    {
        $project = $this->createProjects()->first();
        $firstMetaDataElement = $this->createProjectMetaDataElements(
            1,
            [
                ProjectMetaDataElementModel::PROPERTY_PROJECT => $project,
                ProjectMetaDataElementModel::PROPERTY_POSITION => 1,
            ]
        )->first();
        $secondMetaDataElement = $this->createProjectMetaDataElements(
            1,
            [
                ProjectMetaDataElementModel::PROPERTY_PROJECT => $project,
                ProjectMetaDataElementModel::PROPERTY_POSITION => 2,
            ]
        )->first();
        $thirdMetaDataElement = $this->createProjectMetaDataElements(
            1,
            [
                ProjectMetaDataElementModel::PROPERTY_PROJECT => $project,
                ProjectMetaDataElementModel::PROPERTY_POSITION => 3,
            ]
        )->first();

        $this->clearModelCache();

        $this->doApiCall(
            URL::route('projects.removeProjectMetaDataElement'),
            Request::METHOD_DELETE,
            ['uuid' => $firstMetaDataElement->getUuid()],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(204);
        $projectMetaDataElementRepository = $this->getMetaDataElementsRepository();
        $this->assertEmpty($projectMetaDataElementRepository->findOneByUuid($firstMetaDataElement->getUuid()));
        $this->assertEquals(
            1,
            $projectMetaDataElementRepository->findOneByUuid($secondMetaDataElement->getUuid())->getPosition()
        );
        $this->assertEquals(
            2,
            $projectMetaDataElementRepository->findOneByUuid($thirdMetaDataElement->getUuid())->getPosition()
        );
    }

    /**
     * @return void
     */
    public function testDecreasePositionOnRemoveProjectMetaDataElementOnlyOfTrailingElements(): void
    {
        $project = $this->createProjects()->first();
        $firstMetaDataElement = $this->createProjectMetaDataElements(
            1,
            [
                ProjectMetaDataElementModel::PROPERTY_PROJECT => $project,
                ProjectMetaDataElementModel::PROPERTY_POSITION => 1,
            ]
        )->first();
        $secondMetaDataElement = $this->createProjectMetaDataElements(
            1,
            [
                ProjectMetaDataElementModel::PROPERTY_PROJECT => $project,
                ProjectMetaDataElementModel::PROPERTY_POSITION => 2,
            ]
        )->first();
        $thirdMetaDataElement = $this->createProjectMetaDataElements(
            1,
            [
                ProjectMetaDataElementModel::PROPERTY_PROJECT => $project,
                ProjectMetaDataElementModel::PROPERTY_POSITION => 3,
            ]
        )->first();

        $this->clearModelCache();

        $this->doApiCall(
            URL::route('projects.removeProjectMetaDataElement'),
            Request::METHOD_DELETE,
            ['uuid' => $secondMetaDataElement->getUuid()],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(204);
        $projectMetaDataElementRepository = $this->getMetaDataElementsRepository();
        $this->assertEquals(
            1,
            $projectMetaDataElementRepository->findOneByUuid($firstMetaDataElement->getUuid())->getPosition()
        );
        $this->assertEquals(
            2,
            $projectMetaDataElementRepository->findOneByUuid($thirdMetaDataElement->getUuid())->getPosition()
        );
    }

    /**
     * @return void
     */
    public function testUpdateProjectMetaDataElements(): void
    {
        $projectMetaDataElement = $this->createProjectMetaDataElements()->first();
        $newMetaDataElementData = [
            'uuid'      => $projectMetaDataElement->getUuid(),
            'label'     => $this->getFaker()->word,
            'required'  => $this->getFaker()->boolean,
            'inList'    => $this->getFaker()->boolean,
            'position'  => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ];

        $response = $this->doApiCall(
            URL::route('projects.updateMetaDataElements'),
            Request::METHOD_PATCH,
            [
                'metaDataElements' => [$newMetaDataElementData]
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseOk();
        $responseData = $response->getData(true);
        $this->assertNotEmpty($responseData['metaDataElements']);
        $this
            ->assertMetaDataElementResponseEquals($newMetaDataElementData, $responseData['metaDataElements'][0])
            ->assertMetaDataElementModelValuesEquals(
                $newMetaDataElementData,
                $this->getMetaDataElementsRepository()->findOneByUuid($newMetaDataElementData['uuid'])
            );
    }

    /**
     * @return void
     */
    public function testUpdateProjectMetaDataElementsWithoutElementsToUpdate(): void
    {
        $response = $this->doApiCall(
            URL::route('projects.updateMetaDataElements'),
            Request::METHOD_PATCH,
            [
                'metaDataElements' => []
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseOk();
        $responseData = $response->getData(true);
        $this->assertEmpty($responseData['metaDataElements']);
    }

    /**
     * @return void
     */
    public function testUpdateProjectMetaDataElementsWithoutData(): void
    {
        $projectMetaDataElement = $this->createProjectMetaDataElements()->first();
        $newMetaDataElementData = ['uuid' => $projectMetaDataElement->getUuid()];

        $response = $this->doApiCall(
            URL::route('projects.updateMetaDataElements'),
            Request::METHOD_PATCH,
            [
                'metaDataElements' => [$newMetaDataElementData]
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseOk();
        $responseData = $response->getData(true);
        $this->assertNotEmpty($responseData['metaDataElements']);
        $this
            ->assertMetaDataElementResponseEquals($projectMetaDataElement->toArray(), $responseData['metaDataElements'][0])
            ->assertMetaDataElementModelValuesEquals(
                $projectMetaDataElement->toArray(),
                $this->getMetaDataElementsRepository()->findOneByUuid($newMetaDataElementData['uuid'])
            );
    }

    /**
     * @return void
     */
    public function testUpdateProjectMetaDataElementsWithoutUuid(): void
    {
        $newMetaDataElementData = [
            'label'     => $this->getFaker()->word,
            'required'  => $this->getFaker()->boolean,
            'inList'    => $this->getFaker()->boolean,
            'position'  => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ];

        $response = $this->doApiCall(
            URL::route('projects.updateMetaDataElements'),
            Request::METHOD_PATCH,
            [
                'metaDataElements' => [$newMetaDataElementData]
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.required', $responseData['metaDataElements.0.uuid'][0]);
    }

    /**
     * @return void
     */
    public function testUpdateProjectMetaDataElementsWithInvalidLabel(): void
    {
        $projectMetaDataElement = $this->createProjectMetaDataElements()->first();
        $newMetaDataElementData = [
            'uuid'  => $projectMetaDataElement->getUuid(),
            'label' => $this->getFaker()->numberBetween(),
        ];

        $response = $this->doApiCall(
            URL::route('projects.updateMetaDataElements'),
            Request::METHOD_PATCH,
            [
                'metaDataElements' => [$newMetaDataElementData]
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.string', $responseData['metaDataElements.0.label'][0]);
    }

    /**
     * @return void
     */
    public function testUpdateProjectMetaDataElementsWithInvalidPosition(): void
    {
        $projectMetaDataElement = $this->createProjectMetaDataElements()->first();
        $newMetaDataElementData = [
            'uuid'     => $projectMetaDataElement->getUuid(),
            'position' => $this->getFaker()->word,
        ];

        $response = $this->doApiCall(
            URL::route('projects.updateMetaDataElements'),
            Request::METHOD_PATCH,
            [
                'metaDataElements' => [$newMetaDataElementData]
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.integer', $responseData['metaDataElements.0.position'][0]);
    }

    /**
     * @return void
     */
    public function testUpdateProjectMetaDataElementsWithInvalidRequired(): void
    {
        $projectMetaDataElement = $this->createProjectMetaDataElements()->first();
        $newMetaDataElementData = [
            'uuid'     => $projectMetaDataElement->getUuid(),
            'required' => $this->getFaker()->word,
        ];

        $response = $this->doApiCall(
            URL::route('projects.updateMetaDataElements'),
            Request::METHOD_PATCH,
            [
                'metaDataElements' => [$newMetaDataElementData]
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.boolean', $responseData['metaDataElements.0.required'][0]);
    }

    /**
     * @return void
     */
    public function testUpdateProjectMetaDataElementsWithInvalidInList(): void
    {
        $projectMetaDataElement = $this->createProjectMetaDataElements()->first();
        $newMetaDataElementData = [
            'uuid'   => $projectMetaDataElement->getUuid(),
            'inList' => $this->getFaker()->word,
        ];

        $response = $this->doApiCall(
            URL::route('projects.updateMetaDataElements'),
            Request::METHOD_PATCH,
            [
                'metaDataElements' => [$newMetaDataElementData]
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.boolean', $responseData['metaDataElements.0.inList'][0]);
    }

    /**
     * @return void
     */
    public function testUpdateProjectMetaDataElementsWithInvalidFieldType(): void
    {
        $projectMetaDataElement = $this->createProjectMetaDataElements()->first();
        $newMetaDataElementData = [
            'uuid'      => $projectMetaDataElement->getUuid(),
            'fieldType' => $this->getFaker()->numberBetween(),
        ];

        $response = $this->doApiCall(
            URL::route('projects.updateMetaDataElements'),
            Request::METHOD_PATCH,
            [
                'metaDataElements' => [$newMetaDataElementData]
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.in', $responseData['metaDataElements.0.fieldType'][0]);
    }

    /**
     * @return void
     */
    public function testUpdateProjectMetaDataElementsWithInvalidMetaDataElements(): void
    {
        $projectMetaDataElement = $this->createProjectMetaDataElements()->first();
        $newMetaDataElementData = [
            'uuid'      => $projectMetaDataElement->getUuid(),
            'fieldType' => $this->getFaker()->numberBetween(),
        ];

        $response = $this->doApiCall(
            URL::route('projects.updateMetaDataElements'),
            Request::METHOD_PATCH,
            [
                'metaDataElements' => $this->getFaker()->word,
            ],
            null,
            $this->createAuthHeader($this->createUsers()->first())
        );

        $this->assertResponseStatus(422);
        $responseData = $response->getData(true);
        $this->assertEquals('validation.array', $responseData['metaDataElements'][0]);
    }

    //endregion

    /**
     * @return ProjectRepository
     */
    private function getProjectRepository(): ProjectRepository
    {
        return $this->app->get(ProjectRepository::class);
    }

    /**
     * @return UserRepository
     */
    private function getUserRepository(): UserRepository
    {
        return $this->app->get(UserRepository::class);
    }

    /**
     * @return ProjectInviteRepository
     */
    private function getProjectInviteRepository(): ProjectInviteRepository
    {
        return $this->app->get(ProjectInviteRepository::class);
    }

    /**
     * @return ProjectMetaDataElementRepository
     */
    private function getMetaDataElementsRepository(): ProjectMetaDataElementRepository
    {
        return $this->app->get(ProjectMetaDataElementRepository::class);
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    private function assertQueuedEmail(string $email): self
    {
        $queuedEmails = $this->getEmailService()->getQueuedEmailsByIdentifier('projectInvite');
        $this->assertEquals($email, $queuedEmails[0]['recipient']);
        $this->assertNotEmpty($queuedEmails[0]['context']['inviteUrl']);

        return $this;
    }

    /**
     * @param array $expectedMetaDataElementData
     * @param array $actualMetaDataElementData
     *
     * @return $this
     */
    private function assertMetaDataElementResponseEquals(
        array $expectedMetaDataElementData,
        array $actualMetaDataElementData
    ): self {
        $this->assertEquals($expectedMetaDataElementData['label'], $actualMetaDataElementData['label']);
        $this->assertEquals($expectedMetaDataElementData['required'], $actualMetaDataElementData['required']);
        $this->assertEquals($expectedMetaDataElementData['inList'], $actualMetaDataElementData['inList']);
        $this->assertEquals($expectedMetaDataElementData['position'], $actualMetaDataElementData['position']);
        $this->assertEquals($expectedMetaDataElementData['fieldType'], $actualMetaDataElementData['fieldType']);

        return $this;
    }

    /**
     * @param array                       $expectedMetaDataElementData
     * @param ProjectMetaDataElementModel $projectMetaDataElement
     *
     * @return $this
     */
    private function assertMetaDataElementModelValuesEquals(
        array $expectedMetaDataElementData,
        ProjectMetaDataElementModel $projectMetaDataElement
    ): self {
        $this->assertEquals($expectedMetaDataElementData['label'], $projectMetaDataElement->getLabel());
        $this->assertEquals($expectedMetaDataElementData['required'], $projectMetaDataElement->isRequired());
        $this->assertEquals($expectedMetaDataElementData['inList'], $projectMetaDataElement->isInList());
        $this->assertEquals($expectedMetaDataElementData['position'], $projectMetaDataElement->getPosition());
        $this->assertEquals($expectedMetaDataElementData['fieldType'], $projectMetaDataElement->getFieldType());

       return $this;
    }
}
