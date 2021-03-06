<?php

use App\Exceptions\InvalidParameterException;
use App\Models\Project\ProjectMetaDataElementModel;
use App\Models\Project\ProjectMetaDataElementModelFactory;
use App\Models\Project\ProjectDoctrineModel;
use App\Models\Project\ProjectDoctrineModelFactory;
use App\Models\Project\ProjectInviteModel;
use App\Models\Project\ProjectInviteModelFactory;
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
            'id'               => $this->getFaker()->numberBetween(),
            'label'            => $this->getFaker()->word,
            'user'             => $this->createUserModel(),
            'description'      => $this->getFaker()->text,
            'createdAt'        => $this->getFaker()->dateTime,
            'updatedAt'        => $this->getFaker()->dateTime,
            'deletedAt'        => $this->getFaker()->dateTime,
            'projectInvites'   => [$this->createProjectInviteModel(),],
            'members'          => [$this->createUserModel()],
            'projectMetaDataElements' => [$this->createProjectMetaDataElementModel()],
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
                $data['id'],
                $data['projectInvites'],
                $data['members'],
                $data['projectMetaDataElements']
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
    public function testCreateWithProjectInviteDataArray(): void
    {
        $projectInviteData = [$this->getFaker()->uuid => $this->getFaker()->word];
        $projectInvite = $this->createProjectInviteModel();
        $projectInviteModelFactory = $this->createProjectInviteModelFactory();
        $this->mockModelFactoryCreate($projectInviteModelFactory, $projectInvite, $projectInviteData);
        $uuid = $this->getFaker()->uuid;
        $data = [
            'label'       => $this->getFaker()->word,
            'user'        => $this->createUserModel(),
            'projectInvites' => [$projectInviteData]
        ];

        $this->assertEquals(
            new ProjectDoctrineModel(
                $uuid,
                $data['label'],
                $data['user'],
                null,
                null,
                null,
                null,
                [$projectInvite]
            ),
            $this->getProjectDoctrineModelFactory(
                $this->createUuidFactoryWithUuid($uuid),
                null,
                $projectInviteModelFactory
            )->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithMembersDataArray(): void
    {
        $membersData = [$this->getFaker()->uuid => $this->getFaker()->word];
        $member = $this->createUserModel();
        $userModelFactory = $this->createUserModelFactory();
        $this->mockModelFactoryCreate($userModelFactory, $member, $membersData);
        $data = [
            'label'   => $this->getFaker()->word,
            'user'    => $this->createUserModel(),
            'members' => [$membersData],
        ];

        $this->assertEquals(
            [$member],
            $this->getProjectDoctrineModelFactory(null, $userModelFactory)->create($data)->getMembers()->all()
        );
    }

    /**
     * @return void
     */
    public function testCreateWithMetaDataElementsDataArray(): void
    {
        $projectMetaDataElementsData = [$this->getFaker()->uuid => $this->getFaker()->word];
        $metaDataElement = $this->createProjectMetaDataElementModel();
        $metaDataElementModelFactory = $this->createProjectMetaDataElementModelFactory();
        $this->mockModelFactoryCreate($metaDataElementModelFactory, $metaDataElement, $projectMetaDataElementsData);
        $uuid = $this->getFaker()->uuid;
        $data = [
            'label'            => $this->getFaker()->word,
            'user'             => $this->createUserModel(),
            'projectMetaDataElements' => [$projectMetaDataElementsData],
        ];

        $this->assertEquals(
            new ProjectDoctrineModel(
                $uuid,
                $data['label'],
                $data['user'],
                null,
                null,
                null,
                null,
                [],
                [],
                [$metaDataElement]
            ),
            $this->getProjectDoctrineModelFactory(
                $this->createUuidFactoryWithUuid($uuid),
                null,
                null,
                $metaDataElementModelFactory
            )->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithMissingLabel(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(['user' => $this->createUserModel()]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidLabel(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label' => $this->getFaker()->numberBetween(),
                'user'  => $this->createUserModel(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithMissingUser(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(['label' => $this->getFaker()->word]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidUser(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label' => $this->getFaker()->word,
                'user'  => $this->getFaker()->word,
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
                'label'     => $this->getFaker()->word,
                'user'      => $this->createUserModel(),
                'createdAt' => $this->getFaker()->word,
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
                'label'     => $this->getFaker()->word,
                'user'      => $this->createUserModel(),
                'updatedAt' => $this->getFaker()->word,
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
                'label'     => $this->getFaker()->word,
                'user'      => $this->createUserModel(),
                'deletedAt' => $this->getFaker()->word,
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
                'label' => $this->getFaker()->word,
                'user'  => $this->createUserModel(),
                'id'    => $this->getFaker()->word,
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
    public function testCreateWithEmptyDescription(): void
    {
        $uuid = $this->getFaker()->uuid;
        $data = [
            'label'       => $this->getFaker()->word,
            'user'        => $this->createUserModel(),
            'description' => '',
        ];

        $this->assertEquals(
            new ProjectDoctrineModel(
                $uuid,
                $data['label'],
                $data['user'],
                ''
            ),
            $this->getProjectDoctrineModelFactory($this->createUuidFactoryWithUuid($uuid))->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidProjectInvitesArray(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label'          => $this->getFaker()->word,
                'user'           => $this->createUserModel(),
                'projectInvites' => $this->createProjectInviteModel(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidProjectInvites(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label'          => $this->getFaker()->word,
                'user'           => $this->createUserModel(),
                'projectInvites' => [$this->getFaker()->word],
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidProjectInviteDataArray(): void
    {
        $projectInviteData = [
            $this->getFaker()->uuid => $this->getFaker()->word,
        ];
        $projectInviteModelFactory = $this->createProjectInviteModelFactory();
        $this->mockModelFactoryCreate($projectInviteModelFactory, new InvalidParameterException(), $projectInviteData);

        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory(
            null,
            null,
            $projectInviteModelFactory
        )->create([
            'label'       => $this->getFaker()->word,
            'user'        => $this->createUserModel(),
            'projectInvites' => [$projectInviteData]
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidMembersArray(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label'   => $this->getFaker()->word,
                'user'    => $this->createUserModel(),
                'members' => $this->getFaker()->word,
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidMembers(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create(
            [
                'label'   => $this->getFaker()->word,
                'user'    => $this->createUserModel(),
                'members' => [$this->getFaker()->word],
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidMembersData(): void
    {
        $membersData = [$this->getFaker()->uuid => $this->getFaker()->word];
        $userModelFactory = $this->createUserModelFactory();
        $this->mockModelFactoryCreate($userModelFactory, new InvalidParameterException(), $membersData);
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory(null, $userModelFactory)->create(
            [
                'label'   => $this->getFaker()->word,
                'user'    => $this->createUserModel(),
                'members' => [$membersData],
            ]
        );
    }

    /**
     * @return void
     */
    public function testCreateWithMetaDataElementsArray(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create([
            'label'            => $this->getFaker()->word,
            'user'             => $this->createUserModel(),
            'projectMetaDataElements' => $this->getFaker()->word,
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithMetaDataElements(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->create([
            'label'            => $this->getFaker()->word,
            'user'             => $this->createUserModel(),
            'projectMetaDataElements' => [$this->getFaker()->word],
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidMetaDataElementsData(): void
    {
        $projectMetaDataElementsData = [$this->getFaker()->uuid => $this->getFaker()->word];
        $metaDataElementFactory = $this->createProjectMetaDataElementModelFactory();
        $this->mockModelFactoryCreate($metaDataElementFactory, new InvalidParameterException(), $projectMetaDataElementsData);
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory(null, null, null, $metaDataElementFactory)
            ->create([
                'label'   => $this->getFaker()->word,
                'user'    => $this->createUserModel(),
                'projectMetaDataElements' => [$projectMetaDataElementsData],
            ]);
    }

    /**
     * @return void
     */
    public function testFill(): void
    {
        $data = [
            'id'               => $this->getFaker()->numberBetween(),
            'label'            => $this->getFaker()->word,
            'user'             => $this->createUserModel(),
            'description'      => $this->getFaker()->text,
            'createdAt'        => $this->getFaker()->dateTime,
            'updatedAt'        => $this->getFaker()->dateTime,
            'deletedAt'        => $this->getFaker()->dateTime,
            'projectInvites'   => [$this->createProjectInviteModel()],
            'members'          => [$this->createUserModel()],
            'projectMetaDataElements' => [$this->createProjectMetaDataElementModel()],
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
                $data['deletedAt'],
                $data['projectInvites'],
                $data['members'],
                $data['projectMetaDataElements']
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
    public function testFillWithProjectInviteData(): void
    {
        $data = [
            'projectInvites' => [[$this->getFaker()->uuid => $this->getFaker()->word]],
        ];
        $projectInvite = $this->createProjectInviteModel();
        $projectInviteModelFactory = $this->createProjectInviteModelFactory();
        $this->mockModelFactoryCreate($projectInviteModelFactory, $projectInvite, $data['projectInvites'][0]);
        $project = $this->createProjectDoctrineModel();

        $this->assertEquals(
            $project->setProjectInvites([$projectInvite]),
            $this->getProjectDoctrineModelFactory(null, null, $projectInviteModelFactory)->fill(
                clone $project,
                $data
            )
        );
    }

    /**
     * @return void
     */
    public function testFillWithEmptyProjectInvites(): void
    {
        $project = $this->createProjectDoctrineModel()->setProjectInvites([$this->createProjectInviteModel()]);

        $this->assertEquals(
            clone $project->setProjectInvites([]),
            $this->getProjectDoctrineModelFactory()->fill($project, ['projectInvites' => []])
        );
    }

    /**
     * @return void
     */
    public function testFillWithMembersData(): void
    {
        $data = [
            'members' => [[$this->getFaker()->uuid => $this->getFaker()->word]],
        ];
        $member = $this->createUserModel();
        $userModelFactory = $this->createUserModelFactory();
        $this->mockModelFactoryCreate($userModelFactory, $member, $data['members'][0]);
        $project = $this->createProjectDoctrineModel();

        $this->assertEquals(
            $project->setMembers([$member]),
            $this->getProjectDoctrineModelFactory(null, $userModelFactory)->fill(
                clone $project,
                $data
            )
        );
    }

    /**
     * @return void
     */
    public function testFillWithEmptyMembers(): void
    {
        $project = $this->createProjectDoctrineModel()->setMembers([$this->createUserModel()]);

        $this->assertEquals(
            clone $project->setMembers([]),
            $this->getProjectDoctrineModelFactory()->fill($project, ['members' => []])
        );
    }

    /**
     * @return void
     */
    public function testFillWithMetaDataElementsData(): void
    {
        $data = [
            'projectMetaDataElements' => [[$this->getFaker()->uuid => $this->getFaker()->word]],
        ];
        $metaDataElement = $this->createProjectMetaDataElementModel();
        $metaDataElementFactory = $this->createProjectMetaDataElementModelFactory();
        $this->mockModelFactoryCreate($metaDataElementFactory, $metaDataElement, $data['projectMetaDataElements'][0]);
        $project = $this->createProjectDoctrineModel();

        $this->assertEquals(
            $project->setProjectMetaDataElements([$metaDataElement]),
            $this->getProjectDoctrineModelFactory(
                null,
                null,
                null,
                $metaDataElementFactory
            )->fill(
                clone $project,
                $data
            )
        );
    }

    /**
     * @return void
     */
    public function testFillWithEmptyMetaDataElements(): void
    {
        $project = $this->createProjectDoctrineModel()->setProjectMetaDataElements([$this->createProjectMetaDataElementModel()]);

        $this->assertEquals(
            clone $project->setProjectMetaDataElements([]),
            $this->getProjectDoctrineModelFactory()->fill($project, ['projectMetaDataElements' => []])
        );
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
    public function testFillWithInvalidProjectInvites(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->fill(
            $this->createProjectDoctrineModel(),
            [
                'projectInvites' => $this->getFaker()->word,
            ]
        );
    }

    /**
     * @return void
     */
    public function testFillWithInvalidProjectInvitesData(): void
    {
        $projectData = [$this->getFaker()->uuid => $this->getFaker()->word];
        $projectInviteModelFactory = $this->createProjectInviteModelFactory();
        $this->mockModelFactoryCreate($projectInviteModelFactory, new InvalidParameterException(), $projectData);

        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory(null, null, $projectInviteModelFactory)->fill(
            $this->createProjectDoctrineModel(),
            [
                'projectInvites' => [$projectData],
            ]
        );
    }

    /**
     * @return void
     */
    public function testFillWithInvalidMembersArray(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->fill(
            $this->createProjectDoctrineModel(),
            [
                'members' => $this->getFaker()->word,
            ]
        );
    }

    /**
     * @return void
     */
    public function testFillWithInvalidMembers(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory()->fill(
            $this->createProjectDoctrineModel(),
            [
                'members' => [$this->getFaker()->word],
            ]
        );
    }

    /**
     * @return void
     */
    public function testFillWithInvalidMembersData(): void
    {
        $membersData = [$this->getFaker()->uuid => $this->getFaker()->word];
        $userModelFactory = $this->createUserModelFactory();
        $this->mockModelFactoryCreate($userModelFactory, new InvalidParameterException(), $membersData);

        $this->expectException(InvalidParameterException::class);

        $this->getProjectDoctrineModelFactory(null, $userModelFactory)->fill(
            $this->createProjectDoctrineModel(),
            [
                'members' => [$membersData],
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
     * @param UuidFactory|null                        $uuidFactory
     * @param UserModelFactoryInterface|null          $userModelFactory
     * @param ProjectInviteModelFactory|null          $projectInviteModelFactory
     * @param ProjectMetaDataElementModelFactory|null $metaDataElementModelFactory
     *
     * @return ProjectDoctrineModelFactory|ProjectModelFactory
     */
    private function getProjectDoctrineModelFactory(
        UuidFactory $uuidFactory = null,
        UserModelFactoryInterface $userModelFactory = null,
        ProjectInviteModelFactory $projectInviteModelFactory = null,
        ProjectMetaDataElementModelFactory $metaDataElementModelFactory = null
    ): ProjectDoctrineModelFactory {
        return (new ProjectDoctrineModelFactory($uuidFactory ?: $this->createUuidFactory()))
            ->setUserModelFactory(
                $userModelFactory ?: $this->createUserModelFactory()
            )
            ->setProjectInviteModelFactory(
                $projectInviteModelFactory ?: $this->createProjectInviteModelFactory()
            )
            ->setProjectMetaDataElementModelFactory(
                $metaDataElementModelFactory ?: $this->createProjectMetaDataElementModelFactory()
            );
    }

    /**
     * @param string|null                   $uuid
     * @param string|null                   $label
     * @param UserModelInterface|null       $user
     * @param string|null                   $description
     * @param DateTime|null                 $createdAt
     * @param DateTime|null                 $updatedAt
     * @param DateTime|null                 $deletedAt
     * @param int|null                      $id
     * @param ProjectInviteModel[]          $projectInvites
     * @param UserModelInterface[]          $members
     * @param ProjectMetaDataElementModel[] $projectMetaDataElements
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
        int $id = null,
        array $projectInvites = [],
        array $members = [],
        array $projectMetaDataElements = []
    ): ProjectDoctrineModel {
        return (new ProjectDoctrineModel(
            $uuid ?: $this->getFaker()->uuid,
            $label ?: $this->getFaker()->word,
            $user ?: $this->createUserModel(),
            $description ?: $this->getFaker()->text,
            $createdAt ?: $this->getFaker()->dateTime,
            $updatedAt ?: $this->getFaker()->dateTime,
            $deletedAt ?: $this->getFaker()->dateTime,
            $projectInvites,
            $members,
            $projectMetaDataElements
        ))->setId($id ?: $this->getFaker()->numberBetween());
    }
}
