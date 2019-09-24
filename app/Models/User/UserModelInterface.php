<?php

namespace App\Models\User;

use App\Models\ModelInterface;
use App\Models\Project\ProjectModel;
use App\Models\SoftDeletable;
use App\Models\Timestampable;
use App\Models\Uuidable;
use Illuminate\Support\Collection;
use SPie\LaravelJWT\Contracts\JWTAuthenticatable;

/**
 * Interface UserModelInterface
 *
 * @package App\Models\User
 */
interface UserModelInterface extends ModelInterface, Timestampable, SoftDeletable, JWTAuthenticatable, Uuidable
{
    const PROPERTY_EMAIL          = 'email';
    const PROPERTY_PASSWORD       = 'password';
    const PROPERTY_REFRESH_TOKENS = 'refreshTokens';
    const PROPERTY_PROJECTS       = 'projects';

    /**
     * @param string $email
     *
     * @return UserModelInterface
     */
    public function setEmail(string $email): UserModelInterface;

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @param string $password
     *
     * @return mixed
     */
    public function setPassword(string $password);

    /**
     * @return string
     */
    public function getPassword(): string;

    /**
     * @param RefreshTokenModel[] $refreshTokens
     *
     * @return UserModelInterface
     */
    public function setRefreshTokens(array $refreshTokens): UserModelInterface;

    /**
     * @param RefreshTokenModel $refreshToken
     *
     * @return UserModelInterface
     */
    public function addRefreshToken(RefreshTokenModel $refreshToken): UserModelInterface;

    /**
     * @return RefreshTokenModel[]|Collection
     */
    public function getRefreshTokens(): Collection;

    /**
     * @param ProjectModel[] $projects
     *
     * @return UserModelInterface
     */
    public function setProjects(array $projects): UserModelInterface;

    /**
     * @param ProjectModel $project
     *
     * @return UserModelInterface
     */
    public function addProject(ProjectModel $project): UserModelInterface;

    /**
     * @return ProjectModel[]|Collection
     */
    public function getProjects(): Collection;
}
