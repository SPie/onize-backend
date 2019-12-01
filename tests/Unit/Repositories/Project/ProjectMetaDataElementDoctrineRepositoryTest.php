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
    public function testFindByUuid(): void
    {
        $uuid = $this->getFaker()->uuid;
        $projectMetaDataElement = $this->createProjectMetaDataElementModel();
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, $projectMetaDataElement, ['uuid' => $uuid]);

        $this->assertEquals(
            $projectMetaDataElement,
            $this->getProjectMetaDataElementDoctrineRepository($databaseHandler)->findOneByUuid($uuid)
        );
    }

    /**
     * @return void
     */
    public function testFindByUuidWithoutModel(): void
    {
        $uuid = $this->getFaker()->uuid;
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoad($databaseHandler, null, ['uuid' => $uuid]);

        $this->assertEmpty($this->getProjectMetaDataElementDoctrineRepository($databaseHandler)->findOneByUuid($uuid));
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
        return new ProjectMetaDataElementDoctrineRepository(
            $databaseHandler ?: $this->createDatabaseHandler()
        );
    }
}
