<?php

use App\Exceptions\InvalidParameterException;
use App\Models\Project\ProjectInviteDoctrineModel;
use App\Models\Project\ProjectInviteDoctrineModelFactory;
use App\Models\Project\ProjectModel;
use App\Models\Project\ProjectModelFactory;
use App\Services\Uuid\UuidFactory;
use Test\ModelHelper;
use Test\ProjectHelper;

/**
 * Class ProjectInviteDoctrineModelFactoryTest
 */
final class ProjectInviteDoctrineModelFactoryTest extends TestCase
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
            'id'        => $this->getFaker()->numberBetween(),
            'token'     => $this->getFaker()->uuid,
            'email'     => $this->getFaker()->safeEmail,
            'project'   => $this->createProjectModel(),
            'createdAt' => $this->getFaker()->dateTime,
            'updatedAt' => $this->getFaker()->dateTime,
        ];

        $this->assertEquals(
            $this->createProjectInviteDoctrineModel(
                $uuid,
                $data['token'],
                $data['email'],
                $data['project'],
                $data['createdAt'],
                $data['updatedAt'],
                $data['id']
            ),
            $this->getProjectInviteDoctrineModelFactory($this->createUuidFactoryWithUuid($uuid))->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithRequiredParametersOnly(): void
    {
        $uuid = $this->getFaker()->uuid;
        $data = [
            'token'   => $this->getFaker()->uuid,
            'email'   => $this->getFaker()->safeEmail,
            'project' => $this->createProjectModel(),
        ];

        $this->assertEquals(
            new ProjectInviteDoctrineModel(
                $uuid,
                $data['token'],
                $data['email'],
                $data['project']
            ),
            $this->getProjectInviteDoctrineModelFactory($this->createUuidFactoryWithUuid($uuid))->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithProjectDataArray(): void
    {
        $uuid = $this->getFaker()->uuid;
        $data = [
            'token'   => $this->getFaker()->uuid,
            'email'   => $this->getFaker()->safeEmail,
            'project' => [$this->getFaker()->uuid => $this->getFaker()->word],
        ];
        $project = $this->createProjectModel();
        $projectModelFactory = $this->createProjectModelFactory();
        $this->mockModelFactoryCreate($projectModelFactory, $project, $data['project']);

        $this->assertEquals(
            new ProjectInviteDoctrineModel(
                $uuid,
                $data['token'],
                $data['email'],
                $project
            ),
            $this->getProjectInviteDoctrineModelFactory($this->createUuidFactoryWithUuid($uuid), $projectModelFactory)
                ->create($data)
        );
    }

    /**
     * @return void
     */
    public function testCreateWithMissingToken(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->create([
            'email'   => $this->getFaker()->safeEmail,
            'project' => $this->createProjectModel(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidToken(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->create([
            'token'   => $this->getFaker()->numberBetween(),
            'email'   => $this->getFaker()->safeEmail,
            'project' => $this->createProjectModel(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithMissingEmail(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->create([
            'token'   => $this->getFaker()->uuid,
            'project' => $this->createProjectModel(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidEmail(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->create([
            'token'   => $this->getFaker()->uuid,
            'email'   => $this->getFaker()->word,
            'project' => $this->createProjectModel(),
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithMissingProject(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->create([
            'token'   => $this->getFaker()->uuid,
            'email'   => $this->getFaker()->safeEmail,
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidProject(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->create([
            'token'   => $this->getFaker()->uuid,
            'email'   => $this->getFaker()->safeEmail,
            'project' => $this->getFaker()->word,
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidProjectArray(): void
    {
        $projectData = [$this->getFaker()->uuid => $this->getFaker()->word];
        $projectModelFactory = $this->createProjectModelFactory();
        $this->mockModelFactoryCreate($projectModelFactory, new InvalidParameterException(), $projectData);

        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory(null, $projectModelFactory)->create([
            'token'   => $this->getFaker()->uuid,
            'email'   => $this->getFaker()->safeEmail,
            'project' => $projectData,
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidCreatedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->create([
            'token'     => $this->getFaker()->uuid,
            'email'     => $this->getFaker()->safeEmail,
            'project'   => $this->createProjectModel(),
            'createdAt' => $this->getFaker()->word,
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidUpdatedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->create([
            'token'     => $this->getFaker()->uuid,
            'email'     => $this->getFaker()->safeEmail,
            'project'   => $this->createProjectModel(),
            'updatedAt' => $this->getFaker()->word,
        ]);
    }

    /**
     * @return void
     */
    public function testCreateWithInvalidId(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->create([
            'token'   => $this->getFaker()->uuid,
            'email'   => $this->getFaker()->safeEmail,
            'project' => $this->createProjectModel(),
            'id'      => $this->getFaker()->word,
        ]);
    }

    /**
     * @return void
     */
    public function testFill(): void
    {
        $data = [
            'id'        => $this->getFaker()->numberBetween(),
            'token'     => $this->getFaker()->uuid,
            'email'     => $this->getFaker()->safeEmail,
            'project'   => $this->createProjectModel(),
            'createdAt' => $this->getFaker()->dateTime,
            'updatedAt' => $this->getFaker()->dateTime,
        ];
        $projectInvite = $this->createProjectInviteDoctrineModel();

        $this->assertEquals(
            $this->createProjectInviteDoctrineModel(
                $projectInvite->getUuid(),
                $data['token'],
                $data['email'],
                $data['project'],
                $data['createdAt'],
                $data['updatedAt'],
                $data['id']
            ),
            $this->getProjectInviteDoctrineModelFactory()->fill($projectInvite, $data)
        );
    }

    /**
     * @return void
     */
    public function testFillWithoutData(): void
    {
        $projectInvite = $this->createProjectInviteDoctrineModel();

        $this->assertEquals($projectInvite, $this->getProjectInviteDoctrineModelFactory()->fill($projectInvite, []));
    }

    /**
     * @return void
     */
    public function testFillWithInvalidToken(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->fill($this->createProjectInviteDoctrineModel(), [
            'token' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testFillWithInvalidEmail(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->fill($this->createProjectInviteDoctrineModel(), [
            'email' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testFillWithInvalidProject(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->fill($this->createProjectInviteDoctrineModel(), [
            'project' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testFillWithInvalidProjectData(): void
    {
        $projectData = [$this->getFaker()->uuid => $this->getFaker()->word];
        $projectModelFactory = $this->createProjectModelFactory();
        $this->mockModelFactoryCreate($projectModelFactory, new InvalidParameterException(), $projectData);

        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory(null, $projectModelFactory)->fill(
            $this->createProjectInviteDoctrineModel(),
            [
                'project' => $projectData,
            ]
        );
    }

    /**
     * @return void
     */
    public function testFillWithInvalidCreatedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->fill($this->createProjectInviteDoctrineModel(), [
            'createdAt' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testFillWithInvalidUpdatedAt(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->fill($this->createProjectInviteDoctrineModel(), [
            'updatedAt' => $this->getFaker()->numberBetween(),
        ]);
    }

    /**
     * @return void
     */
    public function testFillWithInvalidId(): void
    {
        $this->expectException(InvalidParameterException::class);

        $this->getProjectInviteDoctrineModelFactory()->fill($this->createProjectInviteDoctrineModel(), [
            'id' => $this->getFaker()->word,
        ]);
    }

    //endregion

    /**
     * @param UuidFactory|null         $uuidFactory
     * @param ProjectModelFactory|null $projectModelFactory
     *
     * @return ProjectInviteDoctrineModelFactory
     */
    private function getProjectInviteDoctrineModelFactory(
        UuidFactory $uuidFactory = null,
        ProjectModelFactory $projectModelFactory = null
    ): ProjectInviteDoctrineModelFactory {
        return (new ProjectInviteDoctrineModelFactory($uuidFactory ?: $this->createUuidFactory()))
            ->setProjectModelFactory($projectModelFactory ?: $this->createProjectModelFactory());
    }

    /**
     * @param string|null       $uuid
     * @param string|null       $token
     * @param string|null       $email
     * @param ProjectModel|null $project
     * @param DateTime|null     $createdAt
     * @param DateTime|null     $updatedAt
     * @param int|null          $id
     *
     * @return ProjectInviteDoctrineModel
     */
    private function createProjectInviteDoctrineModel(
        string $uuid = null,
        string $token = null,
        string $email = null,
        ProjectModel $project = null,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null,
        int $id = null
    ): ProjectInviteDoctrineModel {
        return (new ProjectInviteDoctrineModel(
            $uuid ?: $this->getFaker()->uuid,
            $token ?: $this->getFaker()->uuid,
            $email ?: $this->getFaker()->safeEmail,
            $project ?: $this->createProjectModel(),
            $createdAt ?: $this->getFaker()->dateTime,
            $updatedAt ?: $this->getFaker()->dateTime
        ))->setId($id ?: $this->getFaker()->numberBetween());
    }
}
