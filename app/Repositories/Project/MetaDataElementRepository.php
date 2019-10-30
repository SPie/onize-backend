<?php

namespace App\Repositories\Project;

use App\Models\ModelInterface;
use App\Models\Project\MetaDataElementModel;
use App\Models\Project\ProjectModel;
use App\Repositories\RepositoryInterface;

/**
 * Interface MetaDataElementRepository
 *
 * @package App\Repositories\Project
 */
interface MetaDataElementRepository extends RepositoryInterface
{
    /**
     * @param MetaDataElementModel|ModelInterface $model
     * @param bool           $flush
     *
     * @return MetaDataElementModel|ModelInterface
     */
    public function save(ModelInterface $model, bool $flush = true): ModelInterface;

    /**
     * @param string       $name
     * @param ProjectModel $project
     *
     * @return MetaDataElementModel|null
     */
    public function findByNameAndProject(string $name, ProjectModel $project): ?MetaDataElementModel;
}
