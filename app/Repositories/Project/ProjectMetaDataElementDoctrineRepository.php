<?php

namespace App\Repositories\Project;

use App\Models\ModelInterface;
use App\Models\Project\ProjectMetaDataElementModel;
use App\Models\Project\ProjectModel;
use App\Repositories\AbstractDoctrineRepository;

/**
 * Class ProjectMetaDataElementDoctrineRepository
 *
 * @package App\Repositories\Project
 */
final class ProjectMetaDataElementDoctrineRepository extends AbstractDoctrineRepository implements ProjectMetaDataElementRepository
{
    /**
     * @param string       $name
     * @param ProjectModel $project
     *
     * @return ProjectMetaDataElementModel|ModelInterface|null
     */
    public function findByNameAndProject(string $name, ProjectModel $project): ?ProjectMetaDataElementModel
    {
        return $this->getDatabaseHandler()->load([
            ProjectMetaDataElementModel::PROPERTY_NAME    => $name,
            ProjectMetaDataElementModel::PROPERTY_PROJECT => $project,
        ]);
    }
}
