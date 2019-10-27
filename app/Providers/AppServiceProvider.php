<?php

namespace App\Providers;

use App\Http\Controllers\Project\ProjectsController;
use App\Http\Controllers\User\PasswordResetController;
use App\Http\Middleware\ApiSignature;
use App\Models\Project\MetaDataElementDoctrineModel;
use App\Models\Project\MetaDataElementDoctrineModelFactory;
use App\Models\Project\MetaDataElementModel;
use App\Models\Project\MetaDataElementModelFactory;
use App\Models\Project\ProjectDoctrineModel;
use App\Models\Project\ProjectDoctrineModelFactory;
use App\Models\Project\ProjectInviteDoctrineModel;
use App\Models\Project\ProjectInviteDoctrineModelFactory;
use App\Models\Project\ProjectInviteModel;
use App\Models\Project\ProjectInviteModelFactory;
use App\Models\Project\ProjectModel;
use App\Models\Project\ProjectModelFactory;
use App\Models\User\LoginAttemptDoctrineModel;
use App\Models\User\LoginAttemptDoctrineModelFactory;
use App\Models\User\LoginAttemptModel;
use App\Models\User\LoginAttemptModelFactory;
use App\Models\User\RefreshTokenDoctrineModel;
use App\Models\User\RefreshTokenDoctrineModelFactory;
use App\Models\User\RefreshTokenModel;
use App\Models\User\RefreshTokenModelFactory;
use App\Models\User\UserDoctrineModel;
use App\Models\User\UserDoctrineModelFactory;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\Project\MetaDataElementDoctrineRepository;
use App\Repositories\Project\MetaDataElementRepository;
use App\Repositories\Project\ProjectInviteDoctrineRepository;
use App\Repositories\Project\ProjectInviteRepository;
use App\Repositories\User\LoginAttemptDoctrineRepository;
use App\Repositories\User\LoginAttemptRepository;
use App\Repositories\DatabaseHandler;
use App\Repositories\DoctrineDatabaseHandler;
use App\Repositories\Project\ProjectDoctrineRepository;
use App\Repositories\Project\ProjectRepository;
use App\Repositories\User\RefreshTokenDoctrineRepository;
use App\Repositories\User\RefreshTokenRepository;
use App\Repositories\User\UserDoctrineRepository;
use App\Repositories\User\UserRepository;
use App\Services\Email\EmailService;
use App\Services\Email\QueuedEmailService;
use App\Services\JWT\JWTRefreshTokenRepository;
use App\Services\JWT\JWTService;
use App\Services\JWT\SPieJWTRefreshTokenRepository;
use App\Services\JWT\SPieLaravelJWTService;
use App\Services\Security\LoginThrottlingService;
use App\Services\Security\LoginThrottlingServiceInterface;
use App\Services\Project\ProjectService;
use App\Services\Project\ProjectServiceInterface;
use App\Services\User\UsersService;
use App\Services\User\UsersServiceInterface;
use App\Services\Uuid\RamseyUuidFactory;
use App\Services\Uuid\UuidFactory;
use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\View\Factory;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use Ramsey\Uuid\UuidFactory as ExternalUuidFactory;

/**
 * Class AppServiceProvider
 *
 * @package App\Providers
 */
class AppServiceProvider extends ServiceProvider
{

    //region Register services

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this
            ->registerModels()
            ->registerRepositories()
            ->registerModelFactories()
            ->registerControllers()
            ->registerServices()
            ->registerMiddlewares();
    }

    /**
     * @return $this
     */
    private function registerModels()
    {
        $this->app->bind(UserModelInterface::class, UserDoctrineModel::class);
        $this->app->bind(RefreshTokenModel::class, RefreshTokenDoctrineModel::class);
        $this->app->bind(ProjectModel::class, ProjectDoctrineModel::class);
        $this->app->bind(LoginAttemptModel::class, LoginAttemptDoctrineModel::class);
        $this->app->bind(ProjectInviteModel::class, ProjectInviteDoctrineModel::class);
        $this->app->bind(MetaDataElementModel::class, MetaDataElementDoctrineModel::class);

        return $this;
    }

    /**
     * @return $this
     */
    private function registerRepositories()
    {
        $this->app->bind(DatabaseHandler::class, function (Container $app, array $parameters) {
            return new DoctrineDatabaseHandler($parameters[0], $parameters[1]);
        });

        $this->app->singleton(UserRepository::class, function () {
            return new UserDoctrineRepository($this->makeDatabaseHandler(UserDoctrineModel::class));
        });

        $this->app->singleton(RefreshTokenRepository::class, function () {
            return new RefreshTokenDoctrineRepository($this->makeDatabaseHandler(RefreshTokenDoctrineModel::class));
        });

        $this->app->singleton(ProjectRepository::class, function () {
            return new ProjectDoctrineRepository($this->makeDatabaseHandler(ProjectDoctrineModel::class));
        });

        $this->app->singleton(LoginAttemptRepository::class, function () {
            return new LoginAttemptDoctrineRepository($this->makeDatabaseHandler(LoginAttemptDoctrineModel::class));
        });

        $this->app->singleton(ProjectInviteRepository::class, function () {
            return new ProjectInviteDoctrineRepository($this->makeDatabaseHandler(ProjectInviteDoctrineModel::class));
        });

        $this->app->singleton(MetaDataElementRepository::class, function () {
            return new MetaDataElementDoctrineRepository($this->makeDatabaseHandler(MetaDataElementDoctrineModel::class));
        });

        return $this;
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager(): EntityManager
    {
        return $this->app->get(EntityManager::class);
    }

    /**
     * @param string $className
     *
     * @return DatabaseHandler
     */
    private function makeDatabaseHandler(string $className): DatabaseHandler
    {
        return $this->app->make(DatabaseHandler::class, [$this->getEntityManager(), $className]);
    }

    /**
     * @return $this
     */
    private function registerModelFactories()
    {
        $this->app->singleton(UserModelFactoryInterface::class, UserDoctrineModelFactory::class);
        $this->app->singleton(RefreshTokenModelFactory::class, RefreshTokenDoctrineModelFactory::class);
        $this->app->singleton(ProjectModelFactory::class, ProjectDoctrineModelFactory::class);
        $this->app->singleton(LoginAttemptModelFactory::class, LoginAttemptDoctrineModelFactory::class);
        $this->app->singleton(ProjectInviteModelFactory::class, ProjectInviteDoctrineModelFactory::class);
        $this->app->singleton(MetaDataElementModelFactory::class, MetaDataElementDoctrineModelFactory::class);

        return $this;
    }

    /**
     * @return $this
     */
    private function registerControllers()
    {
        $this->app->bind(PasswordResetController::class, function () {
            return new PasswordResetController($this->app['config']['app.tokenPlaceHolder']);
        });

        $this->app->bind(ProjectsController::class, function () {
            return new ProjectsController(
                $this->getJWTService()->getAuthenticatedUser(),
                $this->getProjectService(),
                $this->app['config']['app.tokenPlaceHolder']
            );
        });

        return $this;
    }

    /**
     * @return $this
     */
    private function registerServices()
    {
        $this->app->singleton(UsersServiceInterface::class, UsersService::class);
        $this->app->singleton(ProjectServiceInterface::class, ProjectService::class);
        $this->app->singleton(JWTService::class, SPieLaravelJWTService::class);
        $this->app->singleton(JWTRefreshTokenRepository::class, SPieJWTRefreshTokenRepository::class);
        $this->app->singleton(EmailService::class, function ($app) {
            return new QueuedEmailService(
                $this->getQueueManager()->connection('rabbitmq'),
                $this->app->make(Factory::class),
                $this->app['config']['email.templatesDir']
            );
        });
        $this->app->singleton(UuidFactory::class, function () {
            return new RamseyUuidFactory(new ExternalUuidFactory());
        });
        $this->app->singleton(LoginThrottlingServiceInterface::class, function ($app) {
            return new LoginThrottlingService(
                $this->app->get(LoginAttemptRepository::class),
                $this->app->get(LoginAttemptModelFactory::class),
                $this->app['config']['security.maxLoginAttempts'],
                $this->app['config']['security.throttlingTimeInMinutes']
            );
        });

        return $this;
    }

    /**
     * @return $this
     */
    private function registerMiddlewares()
    {
        $this->app->bind(ApiSignature::class, function () {
            return new ApiSignature(
                $this->app['config']['middlewares.apiSignature.secret'],
                $this->app['config']['middlewares.apiSignature.algorithm'],
                $this->app['config']['middlewares.apiSignature.toleranceSeconds']
            );
        });

        return $this;
    }

    /**
     * @return JWTService
     */
    private function getJWTService(): JWTService
    {
        return $this->app->get(JWTService::class);
    }

    /**
     * @return ProjectServiceInterface
     */
    private function getProjectService(): ProjectServiceInterface
    {
        return $this->app->get(ProjectServiceInterface::class);
    }

    //endregion

    //region Boot services

    /**
     * @return void
     */
    public function boot()
    {
        $this->bootModelFactories();
    }

    /**
     * @return AppServiceProvider
     */
    private function bootModelFactories(): AppServiceProvider
    {
        $userModelFactory = $this->getUserModelFactory();

        $refreshTokenFactory = $this->getRefreshTokenModelFactory();

        $refreshTokenFactory->setUserModelFactory($userModelFactory);
        $userModelFactory->setRefreshTokenModelFactory($refreshTokenFactory);

        $projectModelFactory = $this->getProjectModelFactory();

        $projectModelFactory->setUserModelFactory($userModelFactory);
        $userModelFactory->setProjectModelFactory($projectModelFactory);

        $projectInviteModelFactory = $this->getProjectInviteModelFactory();

        $projectInviteModelFactory->setProjectModelFactory($projectModelFactory);
        $projectModelFactory->setProjectInviteModelFactory($projectInviteModelFactory);

        $metaDataElementModelFactory = $this->getMetaDataElementModelFactory();

        $metaDataElementModelFactory->setProjectModelFactory($projectModelFactory);
        $projectModelFactory->setMetaDataElementModelFactory($metaDataElementModelFactory);

        return $this;
    }

    /**
     * @return UserModelFactoryInterface
     */
    private function getUserModelFactory(): UserModelFactoryInterface
    {
        return $this->app->get(UserModelFactoryInterface::class);
    }

    /**
     * @return RefreshTokenModelFactory
     */
    private function getRefreshTokenModelFactory(): RefreshTokenModelFactory
    {
        return $this->app->get(RefreshTokenModelFactory::class);
    }

    /**
     * @return ProjectModelFactory
     */
    private function getProjectModelFactory(): ProjectModelFactory
    {
        return $this->app->get(ProjectModelFactory::class);
    }

    /**
     * @return ProjectInviteModelFactory
     */
    private function getProjectInviteModelFactory(): ProjectInviteModelFactory
    {
        return $this->app->get(ProjectInviteModelFactory::class);
    }

    /**
     * @return MetaDataElementModelFactory
     */
    private function getMetaDataElementModelFactory(): MetaDataElementModelFactory
    {
        return $this->app->get(MetaDataElementModelFactory::class);
    }

    //endregion

    /**
     * @return QueueManager
     */
    private function getQueueManager(): QueueManager
    {
        return  $this->app->get('queue');
    }
}
