<?php

use App\Repositories\DatabaseHandler;
use App\Repositories\Project\MetaDataElementDoctrineRepository;
use Test\ModelHelper;
use Test\ProjectHelper;

/**
 * Class MetaDataElementDoctrineRepositoryTest
 */
final class MetaDataElementDoctrineRepositoryTest extends TestCase
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
        $metaDataElement = $this->createMetaDataElementModel();
        $this->mockDatabaseHandlerLoad($databaseHandler, $metaDataElement, ['name' => $name, 'project' => $project]);

        $this->assertEquals(
            $metaDataElement,
            $this->getMetaDataElementDoctrineRepository($databaseHandler)->findByNameAndProject($name, $project)
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
            $this->getMetaDataElementDoctrineRepository($databaseHandler)->findByNameAndProject($name, $project)
        );
    }

    //endregion

    private function getMetaDataElementDoctrineRepository(
        DatabaseHandler $databaseHandler = null
    ): MetaDataElementDoctrineRepository {
        return new MetaDataElementDoctrineRepository($databaseHandler ?: $this->createDatabaseHandler());
    }
}
