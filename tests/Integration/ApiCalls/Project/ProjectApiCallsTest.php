<?php

use App\Models\Project\ProjectModel;
use App\Repositories\Project\ProjectRepository;
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

    //endregion

    /**
     * @return ProjectRepository
     */
    private function getProjectRepository(): ProjectRepository
    {
        return $this->app->get(ProjectRepository::class);
    }
}
