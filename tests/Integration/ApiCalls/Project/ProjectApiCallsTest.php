<?php

use App\Models\Project\ProjectModel;
use App\Repositories\Project\ProjectInviteRepository;
use App\Repositories\Project\ProjectRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Http\Request;
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
        $this->assertEquals($projects->toArray(), $responseData['projects']);
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
}
