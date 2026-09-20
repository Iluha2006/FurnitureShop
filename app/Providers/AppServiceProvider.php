<?php

namespace App\Providers;

use App\Bus\CachedQueryBus;
use App\Bus\CommandBus;
use App\Bus\QueryBus;
use App\Interfaces\CommandBusInterface;
use App\Interfaces\QueryBusInterface;
use App\Models\Furniture;
use App\Repositories\Contracts\FurnitureRepositoryContract;
use App\Repositories\FurnitureRepository;
use App\Search\ElasticsearchObserver;
use Carbon\CarbonImmutable;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
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

        $this->app->bind(CommandBusInterface::class, CommandBus::class);

        $this->app->singleton(Client::class, fn (Application $app): Client => ClientBuilder::create()
            ->setHosts(config('elasticsearch.hosts'))
            ->build());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('elasticsearch.enabled')) {
            Furniture::observe(ElasticsearchObserver::class);
        }

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
