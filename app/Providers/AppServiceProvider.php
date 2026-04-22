<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
            fn () => new \Src\Admin\User\Infrastructure\Repositories\EloquentUserRepository(
                new \App\Models\User()
            )
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

        $this->app->bind(
            \Src\Admin\Trash\Domain\Contracts\TrashRepositoryContract::class,
            \Src\Admin\Trash\Infrastructure\Repositories\EloquentTrashRepository::class
        );

        $this->app->bind(
            \Src\Landing\Subscription\Domain\Contracts\SubscriberRepositoryContract::class,
            \Src\Landing\Subscription\Infrastructure\Repositories\EloquentSubscriberRepository::class
        );

        $this->app->bind(
            \Src\Admin\Design\Domain\DesignRepositoryContract::class,
            \Src\Admin\Design\Infrastructure\Repositories\EloquentDesignRepository::class
        );

        $this->app->bind(
            \Src\Admin\Analytics\Domain\Contracts\AnalyticsRepositoryContract::class,
            \Src\Admin\Analytics\Infrastructure\Repositories\SpatieAnalyticsRepository::class
        );

        $this->app->bind(
            \Src\Admin\Settings\Domain\SettingRepositoryContract::class,
            \Src\Admin\Settings\Infrastructure\Repositories\EloquentSettingRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->input('email', '') . '|' . $request->ip())
                ->response(fn () => response()->json([
                    'error' => 'too_many_attempts',
                    'message' => 'Demasiados intentos. Intenta de nuevo en 1 minuto.',
                ], 429));
        });

        RateLimiter::for('auth_resend', function (Request $request) {
            return Limit::perMinute(3)
                ->by($request->input('email', '') . '|' . $request->ip())
                ->response(fn () => response()->json([
                    'error' => 'too_many_attempts',
                    'message' => 'Demasiados intentos. Intenta de nuevo en 1 minuto.',
                ], 429));
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(fn () => response()->json([
                    'error' => 'too_many_attempts',
                    'message' => 'Has enviado demasiados mensajes. Intenta de nuevo en 1 minuto.',
                ], 429));
        });

        RateLimiter::for('chat', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->ip())
                ->response(fn () => response()->json([
                    'error' => 'too_many_attempts',
                    'message' => 'Demasiados mensajes en poco tiempo. Espera un momento.',
                ], 429));
        });

        $this->registerClientCacheInvalidation();
    }

    /**
     * Wire Eloquent model events to flush /api/client/* cache groups when admin
     * data changes, so the public site reflects edits without manual cache flushes.
     */
    private function registerClientCacheInvalidation(): void
    {
        // Map: ModelClass => [cache groups to flush on save/delete]
        $invalidations = [
            // Blog
            \Src\Admin\Blog\Infrastructure\Models\BlogEloquentModel::class                  => ['blog'],
            \Src\Admin\Blog\Infrastructure\Models\BlogTranslationEloquentModel::class       => ['blog'],
            \Src\Admin\Blog\Infrastructure\Models\BlogCategoryEloquentModel::class          => ['blog'],
            \Src\Admin\Blog\Infrastructure\Models\BlogCategoryTranslationEloquentModel::class => ['blog'],

            // Procedure (also affects navbar, home, contact_data)
            \Src\Admin\Procedure\Infrastructure\Models\ProcedureEloquentModel::class                          => ['procedure', 'navbar', 'contact_data'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedureTranslationEloquentModel::class               => ['procedure', 'navbar', 'contact_data'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedureCategoryEloquentModel::class                  => ['procedure', 'navbar'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedureCategoryTranslationEloquentModel::class       => ['procedure', 'navbar'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedureSectionEloquentModel::class                   => ['procedure'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedureSectionTranslationEloquentModel::class        => ['procedure'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedureFaqEloquentModel::class                       => ['procedure'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedureFaqTranslationEloquentModel::class            => ['procedure'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedurePostoperativeInstructionEloquentModel::class            => ['procedure'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedurePostoperativeInstructionTranslationEloquentModel::class => ['procedure'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedurePreparationStepEloquentModel::class            => ['procedure'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedurePreparationStepTranslationEloquentModel::class => ['procedure'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedureRecoveryPhaseEloquentModel::class              => ['procedure'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedureRecoveryPhaseTranslationEloquentModel::class   => ['procedure'],
            \Src\Admin\Procedure\Infrastructure\Models\ProcedureResultGalleryEloquentModel::class              => ['procedure'],

            // Team / Member
            \Src\Admin\Team\Infrastructure\Models\TeamEloquentModel::class                  => ['member'],
            \Src\Admin\Team\Infrastructure\Models\TeamTranslationEloquentModel::class       => ['member'],
            \Src\Admin\Team\Infrastructure\Models\TeamImageEloquentModel::class             => ['member'],
            \Src\Admin\Team\Infrastructure\Models\TeamImageTranslationEloquentModel::class  => ['member'],

            // Settings (touch maintenance, navbar, contact_data)
            \Src\Admin\Settings\Infrastructure\Models\EloquentSettingModel::class => ['maintenance', 'navbar', 'contact_data'],

            // Design (touch navbar + home)
            \Src\Admin\Design\Infrastructure\Models\EloquentDesignModel::class                 => ['navbar', 'home'],
            \Src\Admin\Design\Infrastructure\Models\EloquentDesignItemModel::class             => ['navbar', 'home'],
            \Src\Admin\Design\Infrastructure\Models\EloquentDesignItemTranslationModel::class  => ['navbar', 'home'],
        ];

        foreach ($invalidations as $modelClass => $groups) {
            $flush = static fn () => \Src\Shared\Infrastructure\Cache\ClientCache::flushGroups(...$groups);
            $modelClass::saved($flush);
            $modelClass::deleted($flush);
        }
    }
}
