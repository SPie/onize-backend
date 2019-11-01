<?php

use App\Models\Project\MetaDataElementDoctrineModel;
use App\Models\Project\ProjectModel;
use Test\ModelHelper;
use Test\ProjectHelper;

/**
 * Class MetaDataElementDoctrineModelTest
 */
final class MetaDataElementDoctrineModelTest extends TestCase
{
    use ModelHelper;
    use ProjectHelper;

    //region Tests

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $projectData = [$this->getFaker()->uuid => $this->getFaker()->word];
        $project = $this->createProjectModel();
        $this->mockModelToArray($project, $projectData, 0);
        $metaDataElement = $this->getMetaDataElementDoctrineModel(null, $project);

        $metaDataElementArray = $metaDataElement->toArray();

        $this->assertEquals($metaDataElement->getName(), $metaDataElementArray['name']);
        $this->assertEquals($projectData, $metaDataElementArray['project']);
        $this->assertEquals($metaDataElement->isRequired(), $metaDataElementArray['required']);
        $this->assertEquals($metaDataElement->isInList(), $metaDataElementArray['inList']);
        $this->assertEquals($metaDataElement->getPosition(), $metaDataElementArray['position']);
    }

    /**
     * @return void
     */
    public function testToArrayWithoutDepth(): void
    {
        $metaDataElement = $this->getMetaDataElementDoctrineModel();

        $metaDataElementArray = $metaDataElement->toArray(0);

        $this->assertEquals($metaDataElement->getName(), $metaDataElementArray['name']);
        $this->assertEquals($metaDataElement->isRequired(), $metaDataElementArray['required']);
        $this->assertEquals($metaDataElement->isInList(), $metaDataElementArray['inList']);
        $this->assertEquals($metaDataElement->getPosition(), $metaDataElementArray['position']);
        $this->assertArrayNotHasKey('project', $metaDataElementArray);
    }

    //endregion

    /**
     * @param string|null       $name
     * @param ProjectModel|null $projectModel
     * @param bool              $required
     * @param bool              $inList
     * @param int|null          $position
     * @param int|null          $id
     *
     * @return MetaDataElementDoctrineModel
     */
    private function getMetaDataElementDoctrineModel(
        string $name = null,
        ProjectModel $projectModel = null,
        bool $required = null,
        bool $inList = null,
        int $position = null,
        int $id = null
    ): MetaDataElementDoctrineModel {
        return (new MetaDataElementDoctrineModel(
            $name ?: $this->getFaker()->uuid,
            $projectModel ?: $this->createProjectModel(),
            ($required !== null) ? $required : $this->getFaker()->boolean,
            ($inList !== null) ? $inList : $this->getFaker()->boolean,
            $position ?: $this->getFaker()->numberBetween()
        ))->setId($id ?: $this->getFaker()->numberBetween());
    }
}
