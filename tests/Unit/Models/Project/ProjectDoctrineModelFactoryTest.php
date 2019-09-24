<?php

use App\Exceptions\InvalidParameterException;
use App\Models\Project\ProjectDoctrineModel;
use App\Models\Project\ProjectDoctrineModelFactory;
use App\Models\Project\ProjectModelFactory;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Services\Uuid\UuidFactory;
use Test\ModelHelper;
use Test\ProjectHelper;
use Test\UserHelper;

/**
 * Class ProjectDoctrineModelFactoryTest
 */
final class ProjectDoctrineModelFactoryTest extends TestCase
{
    use ModelHelper;
    use ProjectHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $uuid = $this->getFaker()->uuid;
        $data = [
            'id'          => $this->getFaker()->numberBetween(),
            'label'       => $this->getFaker()->word,
            'user'        => $this->createUserModel(),
            'description' => $this->getFaker()->text,
            'createdAt'   => $this->getFaker()->dateTime,
            'updatedAt'   => $this->getFaker()->dateTime,
            'deletedAt'   => $this->getFaker()->dateTime,
        ];

        $this->assertEquals(
            $this->createProjectDoctrineModel(
                $uuid,
                $data['label'],
                $data['user'],
                $data['description'],
                $data['createdAt'],
                $data['updatedAt'],
                $data['deletedAt'],
                $data['id']
            ),
            $this->getProjectDoctrineModelFactory($this->createUuidFactoryWithUuid($uuid))->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateOnlyWithRequiredParameters(): void
    {
        $uuid = $this->getFaker()->uuid;
        $data = [
            'label'       => $this->getFaker()->word,
            'user'        => $this->createUserModel(),
        ];

        $this->assertEquals(
            new ProjectDoctrineModel(
                $uuid,
                $data['label'],
                $data['user']
            ),
            $this->getProjectDoctrineModelFactory($this->createUuidFactoryWithUuid($uuid))->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateOnlyWithUserDataArray(): void
    {
        $uuid = $this->getFaker()->uuid;
        $data = [
            'label'       => $this->getFaker()->word,
            'user'        => [$this->getFaker()->uuid => $this->getFaker()->uuid],
        ];
        $user = $this->createUserModel();
        $userModelFactory = $this->createUserModelFactory();
        $this->mockUserModelFactoryCreate($userModelFactory, $user, $data['user']);

        $this->assertEquals(
            new ProjectDoctrineModel(
                $uuid,
                $data['label'],
                $user
            ),
            $this->getProjectDoctrineModelFactory(
                $this->createUuidFactoryWithUuid($uuid),
                $userModelFactory
            )->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithMissingLabel(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'user'        => $this->createUserModel(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidLabel(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label'       => $this->getFaker()->numberBetween(),
                'user'        => $this->createUserModel(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithMissingUser(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label'       => $this->getFaker()->word,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidUser(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label'       => $this->getFaker()->word,
                'user'        => $this->getFaker()->word,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidCreatedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label'       => $this->getFaker()->word,
                'user'        => $this->createUserModel(),
                'createdAt'   => $this->getFaker()->word,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidUpdatedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label'       => $this->getFaker()->word,
                'user'        => $this->createUserModel(),
                'updatedAt'   => $this->getFaker()->word,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidDeletedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label'       => $this->getFaker()->word,
                'user'        => $this->createUserModel(),
                'deletedAt'   => $this->getFaker()->word,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidId(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label'       => $this->getFaker()->word,
                'user'        => $this->createUserModel(),
                'id'          => $this->getFaker()->word,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidDescription(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label'       => $this->getFaker()->word,
                'user'        => $this->createUserModel(),
                'description' => $this->getFaker()->numberBetween(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateOnlyWithUnusedUuid(): void
    {
        $data = [
            'uuid'  => $this->getFaker()->uuid,
            'label' => $this->getFaker()->word,
            'user'  => $this->createUserModel(),
        ];

        $project = $this->getProjectDoctrineModelFactory()->create($data);

        $this->assertNotEquals($data['uuid'], $project->getUuid());
    }


    /**
     * @return void
     */
    public function testFill(): void
    {
        $data = [
            'id'          => $this->getFaker()->numberBetween(),
            'label'       => $this->getFaker()->word,
            'user'        => $this->createUserModel(),
            'description' => $this->getFaker()->text,
            'createdAt'   => $this->getFaker()->dateTime,
            'updatedAt'   => $this->getFaker()->dateTime,
            'deletedAt'   => $this->getFaker()->dateTime,
        ];
        $project = $this->createProjectDoctrineModel();

        $this->assertEquals(
            (new ProjectDoctrineModel(
                $project->getUuid(),
                $data['label'],
                $data['user'],
                $data['description'],
                $data['createdAt'],
                $data['updatedAt'],
                $data['deletedAt']
            ))->setId($data['id']),
            $this->getProjectDoctrineModelFactory()->fill($project, $data)
        );
    }

    /**
     * @return void
     */
    public function testFillWithoutData(): void
    {
        $project = $this->createProjectDoctrineModel();

        $this->assertEquals($project, $this->getProjectDoctrineModelFactory()->fill($project, []));
    }

    /**
     * @return void
     */
    public function testFillWithInvalidLabel(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->fill(
            $this->createProjectDoctrineModel(),
            [
                'label' => $this->getFaker()->numberBetween(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testFillWithInvalidUser(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->fill(
            $this->createProjectDoctrineModel(),
            [
                'user' => $this->getFaker()->uuid,
            ]
        );
    }

    /**
     * @return void
     */
    public function testFillWithEmptyDescription(): void
    {
        $project = $this->createProjectDoctrineModel();

        $this->assertEquals(
            (clone $project)->setDescription(''),
            $this->getProjectDoctrineModelFactory()->fill(
                $project,
                [
                    'description' => ''
                ]
            )
        );
    }

    /**
     * @return void
     */
    public function testFillWithInvalidDescription(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->fill(
            $this->createProjectDoctrineModel(),
            [
                'description' => $this->getFaker()->numberBetween(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testFillWithInvalidCreatedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->fill(
            $this->createProjectDoctrineModel(),
            [
                'createdAt' => $this->getFaker()->numberBetween(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testFillWithInvalidUpdatedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->fill(
            $this->createProjectDoctrineModel(),
            [
                'updatedAt' => $this->getFaker()->numberBetween(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testFillWithInvalidDeletedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->fill(
            $this->createProjectDoctrineModel(),
            [
                'deletedAt' => $this->getFaker()->numberBetween(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testFillWithInvalidId(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->fill(
            $this->createProjectDoctrineModel(),
            [
                'id' => $this->getFaker()->word,
            ]
        );
    }

    /**
     * @return void
     */
    public function testFillUnusedUuid(): void
    {
        $data = ['uuid' => $this->getFaker()->uuid];
        $project = $this->createProjectDoctrineModel();

        $project = $this->getProjectDoctrineModelFactory()->fill($project, $data);

        $this->assertNotEquals($data['uuid'], $project->getUuid());
    }

    //endregion

    /**
     * @param UuidFactory|null               $uuidFactory
     * @param UserModelFactoryInterface|null $userModelFactory
     *
     * @return ProjectDoctrineModelFactory|ProjectModelFactory
     */
    private function getProjectDoctrineModelFactory(
        UuidFactory $uuidFactory = null,
        UserModelFactoryInterface $userModelFactory = null
    ): ProjectDoctrineModelFactory {
        return (new ProjectDoctrineModelFactory($uuidFactory ?: $this->createUuidFactory()))->setUserModelFactory(
            $userModelFactory ?: $this->createUserModelFactory()
        );
    }

    /**
     * @param string|null             $uuid
     * @param string|null             $label
     * @param UserModelInterface|null $user
     * @param string|null             $description
     * @param DateTime|null           $createdAt
     * @param DateTime|null           $updatedAt
     * @param DateTime|null           $deletedAt
     * @param int|null                $id
     *
     * @return ProjectDoctrineModel
     */
    private function createProjectDoctrineModel(
        string $uuid = null,
        string $label = null,
        UserModelInterface $user = null,
        string $description = null,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null,
        \DateTime $deletedAt = null,
        int $id = null
    ): ProjectDoctrineModel {
        return (new ProjectDoctrineModel(
            $uuid ?: $this->getFaker()->uuid,
            $label ?: $this->getFaker()->word,
            $user ?: $this->createUserModel(),
            $description ?: $this->getFaker()->text,
            $createdAt ?: $this->getFaker()->dateTime,
            $updatedAt ?: $this->getFaker()->dateTime,
            $deletedAt ?: $this->getFaker()->dateTime
        ))->setId($id ?: $this->getFaker()->numberBetween());
    }
}
