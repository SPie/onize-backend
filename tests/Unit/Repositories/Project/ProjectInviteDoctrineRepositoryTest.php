<?php

use App\Repositories\DatabaseHandler;
use App\Repositories\Project\ProjectInviteDoctrineRepository;
use Test\ModelHelper;
use Test\ProjectHelper;
use Test\RepositoryHelper;

/**
 * Class ProjectInviteDoctrineRepositoryTest
 */
final class ProjectInviteDoctrineRepositoryTest extends TestCase
{
    use ModelHelper;
    use ProjectHelper;
    use RepositoryHelper;

    //region Tests

    /**
     * @return void
     */
    public function testFindByEmailAndProject(): void
    {
        $email = $this->getFaker()->safeEmail;
        $project = $this->createProjectModel();
        $projectInvite = $this->createProjectInviteModel();
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, $projectInvite, ['email' => $email, 'project' => $project]);

        $this->assertEquals(
            $projectInvite,
            $this->getProjectInviteDoctrineRepository($databaseHandler)->findByEmailAndProject($email, $project)
        );
    }

    /**
     * @return void
     */
    public function testFindByEmailAndProjectWithoutProjectInvite(): void
    {
        $email = $this->getFaker()->safeEmail;
        $project = $this->createProjectModel();
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, null, ['email' => $email, 'project' => $project]);

        $this->assertEmpty(
            $this->getProjectInviteDoctrineRepository($databaseHandler)->findByEmailAndProject($email, $project)
        );
    }

    //endregion

    /**
     * @param DatabaseHandler $databaseHandler
     *
     * @return ProjectInviteDoctrineRepository
     */
    private function getProjectInviteDoctrineRepository(
        DatabaseHandler $databaseHandler = null
    ): ProjectInviteDoctrineRepository {
        return new ProjectInviteDoctrineRepository($databaseHandler ?: $this->createDatabaseHandler());
    }
}
