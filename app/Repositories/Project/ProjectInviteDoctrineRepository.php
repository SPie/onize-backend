<?php

namespace App\Repositories\Project;

use App\Models\ModelInterface;
use App\Models\Project\ProjectInviteModel;
use App\Models\Project\ProjectModel;
use App\Repositories\AbstractDoctrineRepository;

/**
 * Class ProjectInviteDoctrineRepository
 *
 * @package App\Repositories\Project
 */
final class ProjectInviteDoctrineRepository extends AbstractDoctrineRepository implements ProjectInviteRepository
{
    /**
     * @param string       $email
     * @param ProjectModel $project
     *
     * @return ProjectInviteModel|ModelInterface|null
     */
    public function findByEmailAndProject(string $email, ProjectModel $project): ?ProjectInviteModel
    {
        return $this->getDatabaseHandler()->load([
            ProjectInviteModel::PROPERTY_EMAIL   => $email,
            ProjectInviteModel::PROPERTY_PROJECT => $project,
        ]);
    }

    /**
     * @param string $token
     * @param string $email
     *
     * @return ProjectInviteModel|ModelInterface|null
     */
    public function findByTokenAndEmail(string $token, string $email): ?ProjectInviteModel
    {
        return $this->getDatabaseHandler()->load([
            ProjectInviteModel::PROPERTY_TOKEN => $token,
            ProjectInviteModel::PROPERTY_EMAIL => $email,
        ]);
    }
}
