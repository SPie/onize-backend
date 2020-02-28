<?php

namespace App\Repositories\Project;

use App\Models\ModelInterface;
use App\Models\Project\ProjectMetaDataElementDoctrineModel;
use App\Models\Project\ProjectMetaDataElementModel;
use App\Models\Project\ProjectModel;
use App\Repositories\AbstractDoctrineRepository;
use Illuminate\Support\Collection;

/**
 * Class ProjectMetaDataElementDoctrineRepository
 *
 * @package App\Repositories\Project
 */
final class ProjectMetaDataElementDoctrineRepository extends AbstractDoctrineRepository implements ProjectMetaDataElementRepository
{
    /**
     * @param string $uuid
     *
     * @return ProjectMetaDataElementModel|ModelInterface|null
     */
    public function findOneByUuid(string $uuid): ?ProjectMetaDataElementModel
    {
        return $this->getDatabaseHandler()->load([ProjectMetaDataElementModel::PROPERTY_UUID => $uuid]);
    }

    /**
     * @param ProjectModel $project
     *
     * @return Collection
     */
    public function findByProject(ProjectModel $project): Collection
    {
        // TODO: Implement findByProject() method.
    }

    /**
     * @inheritDoc
     */
    public function decreasePosition(int $projectId, int $position): ProjectMetaDataElementRepository
    {
        $this->getDatabaseHandler()->createQueryBuilder()
            ->update(ProjectMetaDataElementDoctrineModel::class, 'pm')
            ->set('pm.position', 'pm.position - 1')
            ->where('pm.project = :projectId')
            ->andWhere('pm.position > :position')
            ->setParameter('projectId', $projectId)
            ->setParameter('position', $position)
            ->getQuery()
            ->execute();

        return $this;
    }
}
