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

        $this->app->bind(
            \Src\Admin\Procedure\Domain\Contracts\ProcedureRepositoryContract::class,
            \Src\Admin\Procedure\Infrastructure\Repositories\EloquentProcedureRepository::class
        );

        $this->app->bind(
            \Src\Admin\Procedure\Domain\Contracts\ProcedureCategoryRepositoryContract::class,
            \Src\Admin\Procedure\Infrastructure\Repositories\EloquentProcedureCategoryRepository::class
        );

        $this->app->bind(
            \Src\Admin\Team\Domain\Contracts\TeamRepositoryContract::class,
            \Src\Admin\Team\Infrastructure\Repositories\EloquentTeamRepository::class
        );

        $this->app->bind(
            \Src\Admin\Audit\Domain\Contracts\AuditRepositoryContract::class,
            \Src\Admin\Audit\Infrastructure\EloquentAuditRepository::class
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
