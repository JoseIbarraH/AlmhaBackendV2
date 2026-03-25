<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \Src\Admin\Auth\Domain\Contracts\AuthenticatorContract::class,
            \Src\Admin\Auth\Infrastructure\Security\JwtLaravelAuthenticator::class
        );

        $this->app->bind(
            \Src\Admin\Auth\Domain\Contracts\AuthorizationContract::class,
            \Src\Admin\Auth\Infrastructure\Security\SpatieAuthorizationAdapter::class
        );

        $this->app->bind(
            \Src\Admin\Role\Domain\Contracts\RoleRepositoryContract::class,
            \Src\Admin\Role\Infrastructure\Repositories\SpatieRoleRepository::class
        );

        $this->app->bind(
            \Src\Shared\Domain\Contracts\TranslatorServiceContract::class,
            \Src\Shared\Infrastructure\Services\GoogleTranslatorService::class
        );

        $this->app->bind(
            \Src\Admin\Blog\Domain\Contracts\BlogRepositoryContract::class,
            \Src\Admin\Blog\Infrastructure\Repositories\EloquentBlogRepository::class
        );

        $this->app->bind(
            \Src\Admin\Blog\Domain\Contracts\BlogCategoryRepositoryContract::class,
            \Src\Admin\Blog\Infrastructure\Repositories\EloquentBlogCategoryRepository::class
        );

        $this->app->bind(
            \Src\Admin\User\Domain\Contracts\UserRepositoryContract::class,
            \Src\Admin\User\Infrastructure\Repositories\EloquentUserRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
