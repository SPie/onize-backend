<?php

namespace App\Repositories\Project;

use App\Models\ModelInterface;
use App\Models\Project\ProjectModel;
use App\Repositories\AbstractDoctrineRepository;

/**
 * Class ProjectDoctrineRepository
 *
 * @package App\Repositories\Project
 */
final class ProjectDoctrineRepository extends AbstractDoctrineRepository implements ProjectRepository
{
    /**
     * @param string $uuid
     *
     * @return ProjectModel|ModelInterface|null
     */
    public function findByUuid(string $uuid): ?ProjectModel
    {
        return $this->getDatabaseHandler()->load([ProjectModel::PROPERTY_UUID => $uuid]);
    }
}
