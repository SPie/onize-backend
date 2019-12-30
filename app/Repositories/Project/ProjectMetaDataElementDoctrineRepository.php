<?php

namespace App\Repositories\Project;

use App\Models\ModelInterface;
use App\Models\Project\ProjectMetaDataElementDoctrineModel;
use App\Models\Project\ProjectMetaDataElementModel;
use App\Repositories\AbstractDoctrineRepository;

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
     * @inheritDoc
     */
    public function decreasePosition(int $projectId, int $position): ProjectMetaDataElementRepository
    {
        $this->getDatabaseHandler()->createQueryBuilder()
            ->update(ProjectMetaDataElementDoctrineModel::class)
            ->set('position', ':newPosition')
            ->where('projectId = :projectId')
            ->andWhere('position > :position')
            ->setParameter('newPosition', 'position - 1')
            ->setParameter('projectId', $projectId)
            ->setParameter('position', $position)
            ->getQuery()
            ->execute();

        return $this;
    }
}
