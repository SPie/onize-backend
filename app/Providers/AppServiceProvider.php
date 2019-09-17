<?php

namespace App\Providers;

use App\Http\Controllers\User\PasswordResetController;
use App\Http\Middleware\ApiSignature;
use App\Models\Project\ProjectDoctrineModel;
use App\Models\Project\ProjectDoctrineModelFactory;
use App\Models\Project\ProjectModel;
use App\Models\Project\ProjectModelFactory;
use App\Models\User\RefreshTokenDoctrineModel;
use App\Models\User\RefreshTokenDoctrineModelFactory;
use App\Models\User\RefreshTokenModel;
use App\Models\User\RefreshTokenModelFactory;
use App\Models\User\UserDoctrineModel;
use App\Models\User\UserDoctrineModelFactory;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\Project\ProjectRepository;
use App\Repositories\User\RefreshTokenRepository;
use App\Repositories\User\UserRepository;
use App\Services\Email\EmailService;
use App\Services\Email\QueuedEmailService;
use App\Services\JWT\JWTRefreshTokenRepository;
use App\Services\JWT\JWTService;
use App\Services\JWT\SPieJWTRefreshTokenRepository;
use App\Services\JWT\SPieLaravelJWTService;
use App\Services\User\UsersService;
use App\Services\User\UsersServiceInterface;
use Doctrine\ORM\EntityManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;

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

        return $this;
    }

    /**
     * @return $this
     */
    private function registerRepositories()
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->app->get(EntityManager::class);

        $this->app->singleton(UserRepository::class, function () use ($entityManager) {
            return $entityManager->getRepository(UserDoctrineModel::class);
        });

        $this->app->singleton(RefreshTokenRepository::class, function () use ($entityManager) {
            return $entityManager->getRepository(RefreshTokenDoctrineModel::class);
        });

        $this->app->singleton(ProjectRepository::class, function () use ($entityManager) {
            return $entityManager->getRepository(ProjectDoctrineModel::class);
        });

        return $this;
    }

    /**
     * @return $this
     */
    private function registerModelFactories()
    {
        $this->app->singleton(UserModelFactoryInterface::class, UserDoctrineModelFactory::class);
        $this->app->singleton(RefreshTokenModelFactory::class, RefreshTokenDoctrineModelFactory::class);
        $this->app->singleton(ProjectModelFactory::class, ProjectDoctrineModelFactory::class);

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

        return $this;
    }

    /**
     * @return $this
     */
    private function registerServices()
    {
        $this->app->singleton(UsersServiceInterface::class, UsersService::class);
        $this->app->singleton(JWTService::class, SPieLaravelJWTService::class);
        $this->app->singleton(JWTRefreshTokenRepository::class, SPieJWTRefreshTokenRepository::class);
        $this->app->singleton(EmailService::class, function ($app) {
            return new QueuedEmailService(
                $this->getQueueManager()->connection('rabbitmq'),
                $this->app->make(Factory::class),
                $this->app['config']['email.templatesDir']
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

    //endregion

    /**
     * @return QueueManager
     */
    private function getQueueManager(): QueueManager
    {
        return  $this->app->get('queue');
    }
}
