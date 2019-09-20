<?php

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

    //endregion
}
