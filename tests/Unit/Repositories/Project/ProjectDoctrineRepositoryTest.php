<?php

use App\Repositories\DatabaseHandler;
use App\Repositories\Project\ProjectDoctrineRepository;
use Test\ModelHelper;
use Test\ProjectHelper;

/**
 * Class ProjectDoctrineRepositoryTest
 */
final class ProjectDoctrineRepositoryTest extends TestCase
{
    use ModelHelper;
    use ProjectHelper;

    //region Tests

    /**
     * @return void
     */
    public function testFindByUuid(): void
    {
        $uuid = $this->getFaker()->uuid;
        $project = $this->createProjectModel();
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, $project, ['uuid' => $uuid]);

        $this->assertEquals($project, $this->getProjectDoctrineRepository($databaseHandler)->findByUuid($uuid));
    }

    /**
     * @return void
     */
    public function testFindByUuidWitoutProject(): void
    {
        $uuid = $this->getFaker()->uuid;
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, null, ['uuid' => $uuid]);

        $this->assertEmpty($this->getProjectDoctrineRepository($databaseHandler)->findByUuid($uuid));
    }

    //endregion

    /**
     * @param DatabaseHandler|null $databaseHandler
     *
     * @return ProjectDoctrineRepository
     */
    private function getProjectDoctrineRepository(DatabaseHandler $databaseHandler = null): ProjectDoctrineRepository
    {
        return new ProjectDoctrineRepository($databaseHandler ?: $this->createDatabaseHandler());
    }
}
