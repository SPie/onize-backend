<?php

namespace App\Repositories\Project;

use App\Models\ModelInterface;
use App\Models\Project\MetaDataElementModel;
use App\Models\Project\ProjectModel;
use App\Repositories\AbstractDoctrineRepository;

/**
 * Class MetaDataElementDoctrineRepository
 *
 * @package App\Repositories\Project
 */
final class MetaDataElementDoctrineRepository extends AbstractDoctrineRepository implements MetaDataElementRepository
{
    /**
     * @param string       $name
     * @param ProjectModel $project
     *
     * @return MetaDataElementModel|ModelInterface|null
     */
    public function findByNameAndProject(string $name, ProjectModel $project): ?MetaDataElementModel
    {
        return $this->getDatabaseHandler()->load([
            MetaDataElementModel::PROPERTY_NAME    => $name,
            MetaDataElementModel::PROPERTY_PROJECT => $project,
        ]);
    }
}
