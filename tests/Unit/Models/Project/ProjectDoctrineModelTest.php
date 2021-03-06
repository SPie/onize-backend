<?php

use App\Models\Project\ProjectDoctrineModel;
use Test\ProjectHelper;
use Test\UserHelper;

/**
 * Class ProjectDoctrineModelTest
 */
final class ProjectDoctrineModelTest extends TestCase
{
    use ProjectHelper;
    use UserHelper;

    //region Tests

    /**
     * @return void
     */
    public function testToArray(): void
    {
        $user = $this->createUserModel();
        $user
            ->shouldReceive('toArray')
            ->andReturn([$this->getFaker()->uuid => $this->getFaker()->uuid]);
        $projectInvite = $this->createProjectInviteModel();
        $projectInvite
            ->shouldReceive('toArray')
            ->andReturn([$this->getFaker()->uuid => $this->getFaker()->word]);
        $member = $this->createUserModel();
        $member
            ->shouldReceive('toArray')
            ->andReturn([$this->getFaker()->uuid => $this->getFaker()->word]);
        $metaDataElement = $this->createProjectMetaDataElementModel();
        $metaDataElement
            ->shouldReceive('toArray')
            ->andReturn([$this->getFaker()->uuid => $this->getFaker()->word]);
        $project = (new ProjectDoctrineModel(
            $this->getFaker()->uuid,
            $this->getFaker()->word,
            $user,
            $this->getFaker()->text,
            $this->getFaker()->dateTime,
            $this->getFaker()->dateTime,
            $this->getFaker()->dateTime,
            [$projectInvite],
            [$member],
            [$metaDataElement]
        ))->setId($this->getFaker()->numberBetween());

        $this->assertEquals(
            [
                'uuid'                    => $project->getUuid(),
                'label'                   => $project->getLabel(),
                'user'                    => $project->getUser()->toArray(),
                'description'             => $project->getDescription(),
                'createdAt'               => (array)$project->getCreatedAt(),
                'updatedAt'               => (array)$project->getUpdatedAt(),
                'deletedAt'               => (array)$project->getDeletedAt(),
                'projectInvites'          => [$projectInvite->toArray()],
                'members'                 => [$member->toArray()],
                'projectMetaDataElements' => [$metaDataElement->toArray()],
            ],
            $project->toArray()
        );
    }

    /**
     * @return void
     */
    public function testToArrayWithoutOptionalParameters(): void
    {
        $user = $this->createUserModel();
        $user
            ->shouldReceive('toArray')
            ->andReturn([$this->getFaker()->uuid => $this->getFaker()->uuid]);
        $project = new ProjectDoctrineModel(
            $this->getFaker()->uuid,
            $this->getFaker()->word,
            $user
        );

        $this->assertEquals(
            [
                'uuid'                    => $project->getUuid(),
                'label'                   => $project->getLabel(),
                'user'                    => $project->getUser()->toArray(),
                'description'             => null,
                'createdAt'               => null,
                'updatedAt'               => null,
                'deletedAt'               => null,
                'projectInvites'          => [],
                'members'                 => [],
                'projectMetaDataElements' => [],
            ],
            $project->toArray()
        );
    }

    /**
     * @return void
     */
    public function testHasMemberWithEmail(): void
    {
        $email = $this->getFaker()->safeEmail;
        $user = $this->createUserModel();
        $this->mockUserModelGetEmail($user, $email);
        $project = new ProjectDoctrineModel(
            $this->getFaker()->uuid,
            $this->getFaker()->word,
            $this->createUserModel(),
            null,
            null,
            null,
            null,
            [],
            [$user]
        );

        $this->assertTrue($project->hasMemberWithEmail($email));
    }

    /**
     * @return void
     */
    public function testHasMemberWithEmailWithoutMemberWithEmail(): void
    {
        $email = $this->getFaker()->safeEmail;
        $user = $this->createUserModel();
        $this->mockUserModelGetEmail($user, 'false' . $email);
        $project = new ProjectDoctrineModel(
            $this->getFaker()->uuid,
            $this->getFaker()->word,
            $this->createUserModel(),
            null,
            null,
            null,
            null,
            [],
            [$user]
        );

        $this->assertFalse($project->hasMemberWithEmail($email));
    }

    /**
     * @return void
     */
    public function testHasMemberWithEmailWithoutMembers(): void
    {
        $project = new ProjectDoctrineModel(
            $this->getFaker()->uuid,
            $this->getFaker()->word,
            $this->createUserModel()
        );

        $this->assertFalse($project->hasMemberWithEmail($this->getFaker()->safeEmail));
    }

    //endregion
}
