<?php

namespace App\Providers;

use App\Bus\CachedQueryBus;
use App\Bus\QueryBus;
use App\Interfaces\QueryBusInterface;
use App\Repositories\Contracts\FurnitureRepositoryContract;
use App\Repositories\FurnitureRepository;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(QueryBusInterface::class, function (Application $app): QueryBusInterface {
            return new CachedQueryBus(
                new QueryBus($app),
                Cache::store(),
            );
        });

        $this->app->bind(FurnitureRepositoryContract::class, FurnitureRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
