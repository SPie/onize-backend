<?php

use App\Models\Project\ProjectDoctrineModel;
use Test\UserHelper;

/**
 * Class ProjectDoctrineModelTest
 */
final class ProjectDoctrineModelTest extends TestCase
{
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
        $project = (new ProjectDoctrineModel(
            $this->getFaker()->uuid,
            $this->getFaker()->word,
            $user,
            $this->getFaker()->text,
            $this->getFaker()->dateTime,
            $this->getFaker()->dateTime,
            $this->getFaker()->dateTime
        ))->setId($this->getFaker()->numberBetween());

        $this->assertEquals(
            [
                'uuid'        => $project->getUuid(),
                'label'       => $project->getLabel(),
                'user'        => $project->getUser()->toArray(),
                'description' => $project->getDescription(),
                'createdAt'   => (array)$project->getCreatedAt(),
                'updatedAt'   => (array)$project->getUpdatedAt(),
                'deletedAt'   => (array)$project->getDeletedAt(),
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
                'uuid'        => $project->getUuid(),
                'label'       => $project->getLabel(),
                'user'        => $project->getUser()->toArray(),
                'description' => null,
                'createdAt'   => null,
                'updatedAt'   => null,
                'deletedAt'   => null,
            ],
            $project->toArray()
        );
    }

    //endregion
}
