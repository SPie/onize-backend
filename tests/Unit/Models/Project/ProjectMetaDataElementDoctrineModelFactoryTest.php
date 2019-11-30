<?php

use App\Exceptions\InvalidParameterException;
use App\Models\Project\ProjectMetaDataElementDoctrineModel;
use App\Models\Project\ProjectMetaDataElementDoctrineModelFactory;
use App\Models\Project\ProjectMetaDataElementModelFactory;
use App\Models\Project\ProjectModel;
use App\Models\Project\ProjectModelFactory;
use App\Services\Uuid\UuidFactory;
use Test\ModelHelper;
use Test\ProjectHelper;

/**
 * Class ProjectMetaDataElementDoctrineModelFactoryTest
 */
final class ProjectMetaDataElementDoctrineModelFactoryTest extends TestCase
{
    use ModelHelper;
    use ProjectHelper;

    //region Tests

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $uuid = $this->getFaker()->uuid;
        $data = [
            'label'     => $this->getFaker()->word,
            'project'   => $this->createProjectModel(),
            'required'  => $this->getFaker()->boolean,
            'inList'    => $this->getFaker()->boolean,
            'position'  => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
            'id'        => $this->getFaker()->numberBetween(),
        ];

        $this->assertEquals(
            $this->createMetaDataElementDoctrineModel(
                $uuid,
                $data['label'],
                $data['project'],
                $data['required'],
                $data['inList'],
                $data['position'],
                $data['fieldType']
            )->setId($data['id']),
            $this->getMetaDataElementDoctrineModelFactory($this->createUuidFactoryWithUuid($uuid))->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateOnlyWithRequiredParameters(): void
    {
        $uuid = $this->getFaker()->uuid;
        $data = [
            'label'     => $this->getFaker()->word,
            'project'   => $this->createProjectModel(),
            'required'  => $this->getFaker()->boolean,
            'inList'    => $this->getFaker()->boolean,
            'position'  => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ];

        $this->assertEquals(
            $this->createMetaDataElementDoctrineModel(
                $uuid,
                $data['label'],
                $data['project'],
                $data['required'],
                $data['inList'],
                $data['position'],
                $data['fieldType']
            ),
            $this->getMetaDataElementDoctrineModelFactory($this->createUuidFactoryWithUuid($uuid))->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithProjectData(): void
    {
        $uuid = $this->getFaker()->uuid;
        $data = [
            'label'     => $this->getFaker()->word,
            'project'   => [$this->getFaker()->uuid => $this->getFaker()->word],
            'required'  => $this->getFaker()->boolean,
            'inList'    => $this->getFaker()->boolean,
            'position'  => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ];
        $project = $this->createProjectModel();
        $projectModelFactory = $this->createProjectModelFactory();
        $this->mockModelFactoryCreate($projectModelFactory, $project, $data['project']);

        $this->assertEquals(
            $this->createMetaDataElementDoctrineModel(
                $uuid,
                $data['label'],
                $project,
                $data['required'],
                $data['inList'],
                $data['position'],
                $data['fieldType']
            ),
            $this->getMetaDataElementDoctrineModelFactory($this->createUuidFactoryWithUuid($uuid), $projectModelFactory)
                ->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithMissingProject(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'label'    => $this->getFaker()->word,
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidProject(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'label'    => $this->getFaker()->word,
            'project'  => $this->getFaker()->word,
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidProjectData(): void
    {
        $projectData = [$this->getFaker()->uuid => $this->getFaker()->word];
        $projectModelFactory = $this->createProjectModelFactory();
        $this->mockModelFactoryCreate($projectModelFactory, new InvalidParameterException(), $projectData);

        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory(null, $projectModelFactory)->create([
            'label'    => $this->getFaker()->word,
            'project'  => $this->getFaker()->word,
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithMissingRequired(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'label'    => $this->getFaker()->word,
            'project'  => $this->createProjectModel(),
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidRequired(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'label'    => $this->getFaker()->word,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->word,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithMissingInList(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'label'    => $this->getFaker()->word,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidInList(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'label'    => $this->getFaker()->word,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->word,
            'position' => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithMissingPosition(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'label'    => $this->getFaker()->word,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'fieldType' => $this->getRandomFieldType(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidPosition(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'label'    => $this->getFaker()->word,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->word,
            'fieldType' => $this->getRandomFieldType(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithoutLabel(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidLabel(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'label'    => $this->getFaker()->numberBetween(),
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithoutFieldType(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'label'    => $this->getFaker()->word,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidFieldType(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'label'    => $this->getFaker()->word,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getFaker()->uuid,
        ]);
    }

    /**
     * @return void
     */
    public function testFill(): void
    {
        $data = [
            'label'    => $this->getFaker()->word,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
            'fieldType' => $this->getRandomFieldType(),
            'id'       => $this->getFaker()->numberBetween(),
        ];
        $metaDataElement = $this->createMetaDataElementDoctrineModel();

        $this->assertEquals(
            $this->createMetaDataElementDoctrineModel(
                $metaDataElement->getUuid(),
                $data['label'],
                $data['project'],
                $data['required'],
                $data['inList'],
                $data['position'],
                $data['fieldType']
            )->setId($data['id']),
            $this->getMetaDataElementDoctrineModelFactory()->fill($metaDataElement, $data)
        );
    }

    /**
     * @return void
     */
    public function testFillWithoutData(): void
    {
        $metaDataElement = $this->createMetaDataElementDoctrineModel();

        $this->assertEquals($metaDataElement, $this->getMetaDataElementDoctrineModelFactory()->fill($metaDataElement, []));
    }

    /**
     * @return void
     */
    public function testFillWithInvalidProject(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->fill($this->createMetaDataElementDoctrineModel(), [
            'project' => $this->getFaker()->word,
        ]);
    }

    /**
     * @return void
     */
    public function testFillWithInvalidProjectData(): void
    {
        $projectData = [$this->getFaker()->uuid => $this->getFaker()->word];
        $projectFactory = $this->createProjectModelFactory();
        $this->mockModelFactoryCreate($projectFactory, new InvalidParameterException(), $projectData);

        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory(null, $projectFactory)->fill(
            $this->createMetaDataElementDoctrineModel(),
            [
                'project' => $projectData,
            ]
        );
    }

    /**
     * @return void
     */
    public function testFillWithInvalidRequired(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->fill($this->createMetaDataElementDoctrineModel(), [
            'required' => $this->getFaker()->word,
        ]);
    }

    /**
     * @return void
     */
    public function testFillWithInvalidInList(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->fill($this->createMetaDataElementDoctrineModel(), [
            'inList' => $this->getFaker()->word,
        ]);
    }

    /**
     * @return void
     */
    public function testFillWithInvalidPosition(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->fill($this->createMetaDataElementDoctrineModel(), [
            'position' => $this->getFaker()->word,
        ]);
    }

    /**
     * @return void
     */
    public function testFillWithInvalidId(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->fill($this->createMetaDataElementDoctrineModel(), [
            'id' => $this->getFaker()->word,
        ]);
    }

    /**
     * @return void
     */
    public function testFillWithInvalidLabel(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->fill($this->createMetaDataElementDoctrineModel(), [
            'label' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testFillWithInvalidFieldType(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->fill($this->createMetaDataElementDoctrineModel(), [
            'fieldType' => $this->getFaker()->uuid,
        ]);
    }

    //endregion

    /**
     * @param UuidFactory|null         $uuidFactory
     * @param ProjectModelFactory|null $projectModelFactory
     *
     * @return ProjectMetaDataElementDoctrineModelFactory|ProjectMetaDataElementModelFactory
     */
    private function getMetaDataElementDoctrineModelFactory(
        UuidFactory $uuidFactory = null,
        ProjectModelFactory $projectModelFactory = null
    ): ProjectMetaDataElementDoctrineModelFactory {
        return (new ProjectMetaDataElementDoctrineModelFactory($uuidFactory  ?: $this->createUuidFactoryWithUuid()))->setProjectModelFactory(
            $projectModelFactory ?: $this->createProjectModelFactory()
        );
    }

    /**
     * @param string|null       $uuid
     * @param string|null       $label
     * @param ProjectModel|null $project
     * @param bool|null         $required
     * @param bool|null         $inList
     * @param int|null          $position
     * @param string|null       $fieldType
     *
     * @return ProjectMetaDataElementDoctrineModel
     */
    private function createMetaDataElementDoctrineModel(
        string $uuid = null,
        string $label = null,
        ProjectModel $project = null,
        bool $required = null,
        bool $inList  = null,
        int $position = null,
        string $fieldType = null
    ): ProjectMetaDataElementDoctrineModel {
        return new ProjectMetaDataElementDoctrineModel(
            $uuid ?: $this->getFaker()->uuid,
            $label ?: $this->getFaker()->word,
            $project ?: $this->createProjectModel(),
            ($required !== null) ? $required : $this->getFaker()->boolean,
            ($inList !== null) ? $inList : $this->getFaker()->boolean,
            $position ?: $this->getFaker()->numberBetween(),
                $fieldType ?: $this->getRandomFieldType()
        );
    }
}
