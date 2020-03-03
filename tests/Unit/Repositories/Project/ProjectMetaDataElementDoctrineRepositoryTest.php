<?php

use App\Models\Project\ProjectMetaDataElementDoctrineModel;
use App\Repositories\DatabaseHandler;
use App\Repositories\Project\ProjectMetaDataElementDoctrineRepository;
use Illuminate\Support\Collection;
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

    /**
     * @return void
     */
    public function testDecreasePosition(): void
    {
        $position = $this->getFaker()->numberBetween();
        $projectId = $this->getFaker()->numberBetween();
        $query = $this->createQuery();
        $queryBuilder = $this->createQueryBuilder();
        $this
            ->mockQueryBuilderUpdate($queryBuilder, ProjectMetaDataElementDoctrineModel::class, 'pm')
            ->mockQueryBuilderSet($queryBuilder, 'pm.position', 'pm.position - 1')
            ->mockQueryBuilderWhere($queryBuilder, 'pm.project = :projectId')
            ->mockQueryBuilderAndWhere($queryBuilder, 'pm.position > :position')
            ->mockQueryBuilderSetParameter($queryBuilder, 'projectId', $projectId)
            ->mockQueryBuilderSetParameter($queryBuilder, 'position', $position)
            ->mockQueryBuilderGetQuery($queryBuilder, $query);
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerCreateQueryBuilder($databaseHandler, $queryBuilder);
        $projectMetaDataElementRepository = $this->getProjectMetaDataElementDoctrineRepository($databaseHandler);

        $projectMetaDataElementRepository->decreasePosition($projectId, $position);

        $this->assertQueryExecute($query);
    }

    /**
     * @return void
     */
    public function testFindByProject(): void
    {
        $project = $this->createProjectModel();
        $projectMetaDataElement = $this->createProjectMetaDataElementModel();
        $databaseHandler = $this->createDatabaseHandler();
        $this->mockDatabaseHandlerLoadAll(
            $databaseHandler,
            new Collection([$projectMetaDataElement]),
            ['project' => $project],
            [],
            null,
            null
        );

        $this->assertEquals(
            new Collection([$projectMetaDataElement]),
            $this->getProjectMetaDataElementDoctrineRepository($databaseHandler)->findByProject($project)
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
        return new ProjectMetaDataElementDoctrineRepository(
            $databaseHandler ?: $this->createDatabaseHandler()
        );
    }
}
