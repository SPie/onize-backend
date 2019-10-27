<?php

use App\Exceptions\InvalidParameterException;
use App\Models\Project\MetaDataElementDoctrineModel;
use App\Models\Project\MetaDataElementDoctrineModelFactory;
use App\Models\Project\MetaDataElementModelFactory;
use App\Models\Project\ProjectModel;
use App\Models\Project\ProjectModelFactory;
use Test\ModelHelper;
use Test\ProjectHelper;

/**
 * Class MetaDataProfileElementDoctrineModelFactoryTest
 */
final class MetaDataElementDoctrineModelFactoryTest extends TestCase
{
    use ModelHelper;
    use ProjectHelper;

    //region Tests

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $data = [
            'name'     => $this->getFaker()->uuid,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
            'id'       => $this->getFaker()->numberBetween(),
        ];

        $this->assertEquals(
            $this->createMetaDataElementDoctrineModel(
                $data['name'],
                $data['project'],
                $data['required'],
                $data['inList'],
                $data['position']
            )->setId($data['id']),
            $this->getMetaDataElementDoctrineModelFactory()->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateOnlyWithRequiredParameters(): void
    {
        $data = [
            'name'     => $this->getFaker()->uuid,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
        ];

        $this->assertEquals(
            $this->createMetaDataElementDoctrineModel(
                $data['name'],
                $data['project'],
                $data['required'],
                $data['inList'],
                $data['position']
            ),
            $this->getMetaDataElementDoctrineModelFactory()->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithProjectData(): void
    {
        $data = [
            'name'     => $this->getFaker()->uuid,
            'project'  => [$this->getFaker()->uuid => $this->getFaker()->word],
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
        ];
        $project = $this->createProjectModel();
        $projectModelFactory = $this->createProjectModelFactory();
        $this->mockModelFactoryCreate($projectModelFactory, $project, $data['project']);

        $this->assertEquals(
            $this->createMetaDataElementDoctrineModel(
                $data['name'],
                $project,
                $data['required'],
                $data['inList'],
                $data['position']
            ),
            $this->getMetaDataElementDoctrineModelFactory($projectModelFactory)->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithMissingName(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidName(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'name'     => $this->getFaker()->numberBetween(),
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithMissingProject(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'name'     => $this->getFaker()->uuid,
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidProject(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'name'     => $this->getFaker()->uuid,
            'project'  => $this->getFaker()->word,
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
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

        $this->getMetaDataElementDoctrineModelFactory($projectModelFactory)->create([
            'name'     => $this->getFaker()->uuid,
            'project'  => $this->getFaker()->word,
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithMissingRequired(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'name'     => $this->getFaker()->uuid,
            'project'  => $this->createProjectModel(),
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidRequired(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'name'     => $this->getFaker()->uuid,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->word,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithMissingInList(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'name'     => $this->getFaker()->uuid,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidInList(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'name'     => $this->getFaker()->uuid,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->word,
            'position' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithMissingPosition(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'name'     => $this->getFaker()->uuid,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidPosition(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->create([
            'name'     => $this->getFaker()->uuid,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->word,
        ]);
    }

    /**
     * @return void
     */
    public function testFill(): void
    {
        $data = [
            'name'     => $this->getFaker()->uuid,
            'project'  => $this->createProjectModel(),
            'required' => $this->getFaker()->boolean,
            'inList'   => $this->getFaker()->boolean,
            'position' => $this->getFaker()->numberBetween(),
            'id'       => $this->getFaker()->numberBetween(),
        ];
        $metaDataElement = $this->createMetaDataElementDoctrineModel();

        $this->assertEquals(
            $this->createMetaDataElementDoctrineModel(
                $data['name'],
                $data['project'],
                $data['required'],
                $data['inList'],
                $data['position']
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
    public function testFillWithInvalidName(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getMetaDataElementDoctrineModelFactory()->fill($this->createMetaDataElementDoctrineModel(), [
            'name' => $this->getFaker()->numberBetween(),
        ]);
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

        $this->getMetaDataElementDoctrineModelFactory($projectFactory)->fill(
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

    //endregion

    /**
     * @param ProjectModelFactory|null $projectModelFactory
     *
     * @return MetaDataElementDoctrineModelFactory|MetaDataElementModelFactory
     */
    private function getMetaDataElementDoctrineModelFactory(
        ProjectModelFactory $projectModelFactory = null
    ): MetaDataElementDoctrineModelFactory {
        return (new MetaDataElementDoctrineModelFactory())->setProjectModelFactory(
            $projectModelFactory ?: $this->createProjectModelFactory()
        );
    }

    /**
     * @param string|null       $name
     * @param ProjectModel|null $project
     * @param bool|null         $required
     * @param bool|null         $inList
     * @param int|null          $position
     *
     * @return MetaDataElementDoctrineModel
     */
    private function createMetaDataElementDoctrineModel(
        string $name = null,
        ProjectModel $project = null,
        bool $required = null,
        bool $inList  = null,
        int $position = null
    ): MetaDataElementDoctrineModel {
        return new MetaDataElementDoctrineModel(
            $name ?: $this->getFaker()->uuid,
            $project ?: $this->createProjectModel(),
            ($required !== null) ? $required : $this->getFaker()->boolean,
            ($inList !== null) ? $inList : $this->getFaker()->boolean,
            $position ?: $this->getFaker()->numberBetween()
        );
    }
}
