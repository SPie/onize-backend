<?php

use App\Models\Project\ProjectMetaDataElementDoctrineModel;
use App\Models\Project\ProjectModel;
use Test\ModelHelper;
use Test\ProjectHelper;

/**
 * Class ProjectMetaDataElementDoctrineModelTest
 */
final class ProjectMetaDataElementDoctrineModelTest extends TestCase
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
        $metaDataElement = $this->getMetaDataElementDoctrineModel(null, null, $project);

        $metaDataElementArray = $metaDataElement->toArray();

        $this->assertEquals($metaDataElement->getName(), $metaDataElementArray['name']);
        $this->assertEquals($metaDataElement->getLabel(), $metaDataElementArray['label']);
        $this->assertEquals($projectData, $metaDataElementArray['project']);
        $this->assertEquals($metaDataElement->isRequired(), $metaDataElementArray['required']);
        $this->assertEquals($metaDataElement->isInList(), $metaDataElementArray['inList']);
        $this->assertEquals($metaDataElement->getPosition(), $metaDataElementArray['position']);
        $this->assertEquals($metaDataElement->getFieldType(), $metaDataElementArray['fieldType']);
    }

    /**
     * @return void
     */
    public function testToArrayWithoutDepth(): void
    {
        $metaDataElement = $this->getMetaDataElementDoctrineModel();

        $metaDataElementArray = $metaDataElement->toArray(0);

        $this->assertEquals($metaDataElement->getName(), $metaDataElementArray['name']);
        $this->assertEquals($metaDataElement->getLabel(), $metaDataElementArray['label']);
        $this->assertEquals($metaDataElement->isRequired(), $metaDataElementArray['required']);
        $this->assertEquals($metaDataElement->isInList(), $metaDataElementArray['inList']);
        $this->assertEquals($metaDataElement->getPosition(), $metaDataElementArray['position']);
        $this->assertEquals($metaDataElement->getFieldType(), $metaDataElementArray['fieldType']);
        $this->assertArrayNotHasKey('project', $metaDataElementArray);
    }

    //endregion

    /**
     * @param string|null       $name
     * @param string|null       $label
     * @param ProjectModel|null $projectModel
     * @param bool              $required
     * @param bool              $inList
     * @param int|null          $position
     * @param string|null       $fieldType
     * @param int|null          $id
     *
     * @return ProjectMetaDataElementDoctrineModel
     */
    private function getMetaDataElementDoctrineModel(
        string $name = null,
        string $label = null,
        ProjectModel $projectModel = null,
        bool $required = null,
        bool $inList = null,
        int $position = null,
        string $fieldType = null,
        int $id = null
    ): ProjectMetaDataElementDoctrineModel {
        return (new ProjectMetaDataElementDoctrineModel(
            $name ?: $this->getFaker()->uuid,
            $label ?: $this->getFaker()->word,
            $projectModel ?: $this->createProjectModel(),
            ($required !== null) ? $required : $this->getFaker()->boolean,
            ($inList !== null) ? $inList : $this->getFaker()->boolean,
            $position ?: $this->getFaker()->numberBetween(),
            $fieldType ?: 'text'
        ))->setId($id ?: $this->getFaker()->numberBetween());
    }
}
