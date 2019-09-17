<?php

namespace App\Models\User;

use App\Models\ModelFactoryInterface;
use App\Models\ModelInterface;
use App\Models\Project\ProjectModelFactory;

/**
 * Interface UserModelFactoryInterface
 *
 * @package App\Models\User
 */
interface UserModelFactoryInterface extends ModelFactoryInterface
{

    /**
     * @param RefreshTokenModelFactory $refreshTokenModelFactory
     *
     * @return UserModelFactoryInterface
     */
    public function setRefreshTokenModelFactory(RefreshTokenModelFactory $refreshTokenModelFactory): UserModelFactoryInterface;

    /**
     * @param ProjectModelFactory $projectModelFactory
     *
     * @return UserModelFactoryInterface
     */
    public function setProjectModelFactory(ProjectModelFactory $projectModelFactory): UserModelFactoryInterface;

    /**
     * @param array $data
     *
     * @return UserModelInterface|ModelInterface
     */
    public function create(array $data): ModelInterface;

    /**
     * @param ModelInterface $model
     * @param array          $data
     *
     * @return UserModelInterface|ModelInterface
     */
    public function fill(ModelInterface $model, array $data): ModelInterface;
}
