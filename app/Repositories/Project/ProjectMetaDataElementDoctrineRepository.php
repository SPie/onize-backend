<?php

namespace App\Repositories\Project;

use App\Models\ModelInterface;
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
}
