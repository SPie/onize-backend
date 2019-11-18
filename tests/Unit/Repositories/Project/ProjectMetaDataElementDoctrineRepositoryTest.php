<?php

use App\Repositories\DatabaseHandler;
use App\Repositories\Project\ProjectMetaDataElementDoctrineRepository;
use Test\ModelHelper;
use Test\ProjectHelper;

/**
 * Class ProjectMetaDataElementDoctrineRepositoryTest
 */
final class ProjectMetaDataElementDoctrineRepositoryTest extends TestCase
{
    use ModelHelper;
    use ProjectHelper;

    //region Tests

    /**
     * @return void
     */
    public function testFindByNameAndProject(): void
    {
        $name = $this->getFaker()->uuid;
        $project = $this->createProjectModel();
        $databaseHandler = $this->createDatabaseHandler();
        $metaDataElement = $this->createProjectMetaDataElementModel();
        $this->mockDatabaseHandlerLoad($databaseHandler, $metaDataElement, ['name' => $name, 'project' => $project]);

        $this->assertEquals(
            $metaDataElement,
            $this->getProjectMetaDataElementDoctrineRepository($databaseHandler)->findByNameAndProject($name, $project)
        );
    }

    /**
     * @return void
     */
    public function testFindByNameAndProjectWithoutMetaDataElement(): void
    {
        $name = $this->getFaker()->uuid;
        $project = $this->createProjectModel();
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, null, ['name' => $name, 'project' => $project]);

        $this->assertNull(
            $this->getProjectMetaDataElementDoctrineRepository($databaseHandler)->findByNameAndProject($name, $project)
        );
    }

    //endregion

    /**
     * @param DatabaseHandler|null $databaseHandler
     *
     * @return ProjectMetaDataElementDoctrineRepository
     */
    private function getProjectMetaDataElementDoctrineRepository(
        DatabaseHandler $databaseHandler = null
    ): ProjectMetaDataElementDoctrineRepository {
        return new ProjectMetaDataElementDoctrineRepository($databaseHandler ?: $this->createDatabaseHandler());
    }
}
