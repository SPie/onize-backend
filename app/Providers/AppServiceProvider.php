<?php

namespace App\Providers;

use App\Http\Middleware\ApiSignature;
use App\Models\User\PasswordResetTokenDoctrineModelFactory;
use App\Models\User\PasswordResetTokenModelFactory;
use App\Models\User\RefreshTokenDoctrineModel;
use App\Models\User\RefreshTokenDoctrineModelFactory;
use App\Models\User\RefreshTokenModelFactory;
use App\Models\User\UserDoctrineModel;
use App\Models\User\UserDoctrineModelFactory;
use App\Models\User\UserModelFactoryInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\User\PasswordResetTokenDoctrineRepository;
use App\Repositories\User\PasswordResetTokenRepository;
use App\Repositories\User\RefreshTokenRepository;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\JWT\JWTRefreshTokenRepository;
use App\Services\JWT\JWTService;
use App\Services\JWT\SPieJWTRefreshTokenRepository;
use App\Services\JWT\SPieLaravelJWTService;
use App\Services\User\UsersService;
use App\Services\User\UsersServiceInterface;
use Doctrine\ORM\EntityManager;
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
            ->registerServices()
            ->registerMiddlewares();
    }

    /**
     * @return $this
     */
    private function registerModels()
    {
        $this->app->bind(UserModelInterface::class, UserDoctrineModel::class);

        return $this;
    }

    /**
     * @return $this
     */
    private function registerRepositories()
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->app->get(EntityManager::class);

        $this->app->singleton(UserRepositoryInterface::class, function () use ($entityManager) {
            return $entityManager->getRepository(UserDoctrineModel::class);
        });

        $this->app->singleton(RefreshTokenRepository::class, function () use ($entityManager) {
            return $entityManager->getRepository(RefreshTokenDoctrineModel::class);
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

    private function bootModelFactories(): AppServiceProvider
    {
        $userModelFactory = $this->app->get(UserModelFactoryInterface::class);
        $refreshTokenFactory = $this->app->get(RefreshTokenModelFactory::class);

        $userModelFactory->setRefreshTokenModelFactory($refreshTokenFactory);
        $refreshTokenFactory->setUserModelFactory($userModelFactory);

        return $this;
    }

    //endregion
}
