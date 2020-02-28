<?php

namespace App\Repositories\Project;

use App\Models\ModelInterface;
use App\Models\Project\ProjectMetaDataElementModel;
use App\Models\Project\ProjectModel;
use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Interface ProjectMetaDataElementRepository
 *
 * @package App\Repositories\Project
 */
interface ProjectMetaDataElementRepository extends RepositoryInterface
{
    /**
     * @param ProjectMetaDataElementModel|ModelInterface $model
     * @param bool                                       $flush
     *
     * @return ProjectMetaDataElementModel|ModelInterface
     */
    public function save(ModelInterface $model, bool $flush = true): ModelInterface;

    /**
     * @param string $uuid
     *
     * @return ProjectMetaDataElementModel|null
     */
    public function findOneByUuid(string $uuid): ?ProjectMetaDataElementModel;

    /**
     * @param ProjectModel $project
     *
     * @return ProjectModel[]|Collection
     */
    public function findByProject(ProjectModel $project): Collection;

    /**
     * @param int $projectId
     * @param int $position
     *
     * @return $this
     */
    public function decreasePosition(int $projectId, int $position): self;
}
