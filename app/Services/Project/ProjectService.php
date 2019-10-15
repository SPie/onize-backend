<?php

namespace App\Services\Project;

use App\Exceptions\Auth\NotAllowedException;
use App\Exceptions\ModelNotFoundException;
use App\Exceptions\Project\UserAlreadyMemberException;
use App\Models\ModelInterface;
use App\Models\Project\ProjectInviteModel;
use App\Models\Project\ProjectInviteModelFactory;
use App\Models\Project\ProjectModel;
use App\Models\Project\ProjectModelFactory;
use App\Models\User\UserModelInterface;
use App\Repositories\Project\ProjectInviteRepository;
use App\Repositories\Project\ProjectRepository;

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
     * @var ProjectInviteRepository
     */
    private $projectInviteRepository;

    /**
     * @var ProjectInviteModelFactory
     */
    private $projectInviteModelFactory;

    /**
     * ProjectService constructor.
     *
     * @param ProjectRepository         $projectRepository
     * @param ProjectModelFactory       $projectModelFactory
     * @param ProjectInviteRepository   $projectInviteRepository
     * @param ProjectInviteModelFactory $projectInviteModelFactory
     */
    public function __construct(
        ProjectRepository $projectRepository,
        ProjectModelFactory $projectModelFactory,
        ProjectInviteRepository $projectInviteRepository,
        ProjectInviteModelFactory $projectInviteModelFactory
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectModelFactory = $projectModelFactory;
        $this->projectInviteRepository = $projectInviteRepository;
        $this->projectInviteModelFactory = $projectInviteModelFactory;
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
     * @return ProjectInviteRepository
     */
    private function getProjectInviteRepository(): ProjectInviteRepository
    {
        return $this->projectInviteRepository;
    }

    /**
     * @return ProjectInviteModelFactory
     */
    private function getProjectInviteModelFactory(): ProjectInviteModelFactory
    {
        return $this->projectInviteModelFactory;
    }

    /**
     * @param string $uuid
     *
     * @return ProjectModel
     */
    public function getProject(string $uuid): ProjectModel
    {
        $project = $this->getProjectRepository()->findByUuid($uuid);
        if (!$project) {
            throw new ModelNotFoundException(ProjectModel::class, $uuid);
        }

        return $project;
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

    /**
     * @param string $uuid
     * @param string $email
     *
     * @return ProjectInviteModel|ModelInterface
     */
    public function invite(string $uuid, string $email): ProjectInviteModel
    {
        $project = $this->getProject($uuid);
        if ($this->isUserAlreadyMember($project, $email)) {
            throw new UserAlreadyMemberException();
        }

        $projectInvite = $this->getProjectInviteRepository()->findByEmailAndProject($email, $project);

        return $this->getProjectInviteRepository()->save(
            $projectInvite
                ? $this->updateProjectInvite($projectInvite, $email)
                : $this->createNewProjectInvite($email, $project)
        );
    }

    /**
     * @param ProjectModel $project
     * @param string       $email
     *
     * @return bool
     */
    private function isUserAlreadyMember(ProjectModel $project, string $email): bool
    {
        return $project->hasMemberWithEmail($email) || $project->getUser()->getEmail() == $email;
    }

    /**
     * @param string       $email
     * @param ProjectModel $project
     *
     * @return ProjectInviteModel
     */
    private function createNewProjectInvite(string $email, ProjectModel $project): ProjectInviteModel
    {
        return $this->getProjectInviteModelFactory()->create([
            ProjectInviteModel::PROPERTY_TOKEN   => $this->createToken($email),
            ProjectInviteModel::PROPERTY_EMAIL   => $email,
            ProjectInviteModel::PROPERTY_PROJECT => $project,
        ]);
    }

    /**
     * @param ProjectInviteModel $projectInvite
     * @param string             $email
     *
     * @return ProjectInviteModel
     */
    private function updateProjectInvite(ProjectInviteModel $projectInvite, string $email): ProjectInviteModel
    {
        return $this->getProjectInviteModelFactory()->fill(
            $projectInvite,
            [ProjectInviteModel::PROPERTY_TOKEN => $this->createToken($email)]
        );
    }

    /**
     * @param string $email
     *
     * @return string
     */
    private function createToken(string $email): string
    {
        return \md5(\time() . $email . \mt_rand());
    }
}
