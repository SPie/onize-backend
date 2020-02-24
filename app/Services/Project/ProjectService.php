<?php

namespace App\Services\Project;

use App\Exceptions\Auth\NotAllowedException;
use App\Exceptions\ModelNotFoundException;
use App\Exceptions\Project\InvalidInviteTokenException;
use App\Exceptions\Project\UserAlreadyMemberException;
use App\Models\ModelInterface;
use App\Models\Project\ProjectMetaDataElementModel;
use App\Models\Project\ProjectMetaDataElementModelFactory;
use App\Models\Project\ProjectInviteModel;
use App\Models\Project\ProjectInviteModelFactory;
use App\Models\Project\ProjectModel;
use App\Models\Project\ProjectModelFactory;
use App\Models\User\UserModelInterface;
use App\Repositories\Project\ProjectMetaDataElementRepository;
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
     * @var ProjectMetaDataElementRepository
     */
    private $projectMetaDataElementRepository;

    /**
     * @var ProjectMetaDataElementModelFactory
     */
    private $projectMetaDataElementModelFactory;

    /**
     * ProjectService constructor.
     *
     * @param ProjectRepository                  $projectRepository
     * @param ProjectModelFactory                $projectModelFactory
     * @param ProjectInviteRepository            $projectInviteRepository
     * @param ProjectInviteModelFactory          $projectInviteModelFactory
     * @param ProjectMetaDataElementRepository   $projectMetaDataElementRepository
     * @param ProjectMetaDataElementModelFactory $projectMetaDataElementModelFactory
     */
    public function __construct(
        ProjectRepository $projectRepository,
        ProjectModelFactory $projectModelFactory,
        ProjectInviteRepository $projectInviteRepository,
        ProjectInviteModelFactory $projectInviteModelFactory,
        ProjectMetaDataElementRepository $projectMetaDataElementRepository,
        ProjectMetaDataElementModelFactory $projectMetaDataElementModelFactory
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectModelFactory = $projectModelFactory;
        $this->projectInviteRepository = $projectInviteRepository;
        $this->projectInviteModelFactory = $projectInviteModelFactory;
        $this->projectMetaDataElementRepository = $projectMetaDataElementRepository;
        $this->projectMetaDataElementModelFactory = $projectMetaDataElementModelFactory;
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
     * @return ProjectMetaDataElementRepository
     */
    private function getProjectMetaDataElementRepository(): ProjectMetaDataElementRepository
    {
        return $this->projectMetaDataElementRepository;
    }

    /**
     * @return ProjectMetaDataElementModelFactory
     */
    private function getProjectMetaDataElementModelFactory(): ProjectMetaDataElementModelFactory
    {
        return $this->projectMetaDataElementModelFactory;
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

    /**
     * @param string $token
     * @param string $email
     *
     * @return ProjectInviteModel
     */
    public function verifyInvite(string $token, string $email): ProjectInviteModel
    {
        $projectInvite = $this->getProjectInviteRepository()->findByTokenAndEmail($token, $email);
        if (!$projectInvite) {
            throw new ModelNotFoundException(ProjectInviteModel::class, $token . ',' . $email);
        }

        return $projectInvite;
    }

    /**
     * @param ProjectModel $project
     *
     * @return array
     */
    public function getMetaDataValidators(ProjectModel $project): array
    {
        // TODO: Implement getMetaDataValidators() method.
    }

    /**
     * @param ProjectInviteModel $projectInvite
     * @param UserModelInterface $user
     * @param array              $metaData
     *
     * @return ProjectServiceInterface
     */
    public function finishInvite(ProjectInviteModel $projectInvite, UserModelInterface $user, array $metaData): ProjectServiceInterface
    {
        // TODO
    }

    /**
     * @param string $uuid
     *
     * @return ProjectMetaDataElementModel
     *
     * @throws ModelNotFoundException
     */
    public function getMetaDataElement(string $uuid): ProjectMetaDataElementModel
    {
        $metaDataElement = $this->getProjectMetaDataElementRepository()->findOneByUuid($uuid);
        if (!$metaDataElement) {
            throw new ModelNotFoundException(ProjectMetaDataElementModel::class, $uuid);
        }

        return $metaDataElement;
    }

    /**
     * @param string $uuid
     * @param array  $metaDataElements
     *
     * @return ProjectMetaDataElementModel[]
     */
    public function createMetaDataElements(string $uuid, array $metaDataElements): array
    {
        $project = $this->getProjectRepository()->findByUuid($uuid);
        if (!$project) {
            throw new ModelNotFoundException(ProjectModel::class, $uuid);
        }

        $metaDataElementModels = [];
        foreach ($metaDataElements as $index => $metaDataElement) {
            $metaDataElementModels[] = $this->createNewMetaDataElement($metaDataElement, $project);
        }

        $this->getProjectMetaDataElementRepository()->flush();

        return $metaDataElementModels;
    }

    /**
     * @param array        $metaDataElementData
     * @param ProjectModel $project
     *
     * @return ProjectMetaDataElementModel
     */
    private function createNewMetaDataElement(array $metaDataElementData, ProjectModel $project): ProjectMetaDataElementModel
    {
        return $this->getProjectMetaDataElementRepository()->save(
            $this->getProjectMetaDataElementModelFactory()->create(
                \array_merge($metaDataElementData, [ProjectMetaDataElementModel::PROPERTY_PROJECT => $project])
            ),
            false
        );
    }

    /**
     * @param array $metaDataElementsData
     *
     * @return ProjectMetaDataElementModel[]
     */
    public function updateMetaDataElements(array $metaDataElementsData): array
    {
        $metaDataElements = \array_map(
            function (array $metaDataElementData) {
                return $this->getProjectMetaDataElementRepository()->save(
                    $this->getProjectMetaDataElementModelFactory()->fill(
                        $this->getMetaDataElement($metaDataElementData[ProjectMetaDataElementModel::PROPERTY_UUID]),
                        $metaDataElementData
                    ),
                    false
                );
            },
            $metaDataElementsData
        );

        $this->getProjectMetaDataElementRepository()->flush();

        return $metaDataElements;
    }

    /**
     * @param string $uuid
     *
     * @return ProjectServiceInterface
     */
    public function removeProjectMetaDataElement(string $uuid): ProjectServiceInterface
    {
        $projectMetaDataElement = $this->getProjectMetaDataElementRepository()->findOneByUuid($uuid);
        if (!$projectMetaDataElement) {
            throw new ModelNotFoundException(ProjectMetaDataElementModel::class, $uuid);
        }

        $this->getProjectMetaDataElementRepository()
            ->decreasePosition($projectMetaDataElement->getProject()->getId(), $projectMetaDataElement->getPosition())
            ->delete($projectMetaDataElement);

        return $this;
    }
}
