<?php

namespace App\Services\Project;

use App\Exceptions\Auth\NotAllowedException;
use App\Exceptions\ModelNotFoundException;
use App\Models\Project\ProjectModel;
use App\Models\Project\ProjectModelFactory;
use App\Models\User\UserModelInterface;
use App\Repositories\Project\ProjectRepository;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class ProjectService
 *
 * @package App\Services\Project
 */
final class ProjectService implements ProjectServiceInterface
{
    /**
     * @var ProjectRepository
     */
    private $projectRepository;

    /**
     * @var ProjectModelFactory
     */
    private $projectModelFactory;

    /**
     * ProjectService constructor.
     *
     * @param ProjectRepository   $projectRepository
     * @param ProjectModelFactory $projectModelFactory
     */
    public function __construct(ProjectRepository $projectRepository, ProjectModelFactory $projectModelFactory)
    {
        $this->projectRepository = $projectRepository;
        $this->projectModelFactory = $projectModelFactory;
    }

    /**
     * @return ProjectRepository
     */
    private function getProjectRepository(): ProjectRepository
    {
        return $this->projectRepository;
    }

    /**
     * @return ProjectModelFactory
     */
    private function getProjectModelFactory(): ProjectModelFactory
    {
        return $this->projectModelFactory;
    }
    /**
     * @param array              $projectData
     * @param UserModelInterface $user
     *
     * @return ProjectModel
     */
    public function createProject(array $projectData, UserModelInterface $user): ProjectModel
    {
        return $this->getProjectRepository()->save(
            $this->getProjectModelFactory()->create(
                \array_merge(
                    $projectData,
                    [ProjectModel::PROPERTY_USER => $user]
                )
            )
        );
    }

    /**
     * @param string             $uuid
     * @param UserModelInterface $authenticatedUser
     *
     * @return ProjectServiceInterface
     *
     * @throws ModelNotFoundException
     */
    public function removeProject(string $uuid, UserModelInterface $authenticatedUser): ProjectServiceInterface
    {
        $project = $this->getProjectRepository()->findByUuid($uuid);
        if (!$project) {
            throw new ModelNotFoundException(ProjectModel::class, $uuid);
        }

        if ($project->getUser()->getId() != $authenticatedUser->getId()) {
            throw new NotAllowedException();
        }

        $this->getProjectRepository()->delete($project);

        return $this;
    }
}
