<?php

namespace BoostBrains\LaravelCodeCheck\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelCodeCheckServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__, 2) . '/config/routeanalyzer.php',
            'routeanalyzer'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                \BoostBrains\LaravelCodeCheck\Console\Commands\AnalyzeRoutesCommand::class,
                \BoostBrains\LaravelCodeCheck\Console\Commands\AnalyzeRouteTestMatrixCommand::class,
                \BoostBrains\LaravelCodeCheck\Console\Commands\RoutesCodeCommand::class,
                \BoostBrains\LaravelCodeCheck\Console\Commands\RoutesDocSwaggerCommand::class,
                \BoostBrains\LaravelCodeCheck\Console\Commands\GenerateRouteTestMatrixCommand::class,
                \BoostBrains\LaravelCodeCheck\Console\Commands\RunCleanCodeNoRouteDependantCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            dirname(__DIR__, 2) . '/config/routeanalyzer.php' => config_path('routeanalyzer.php'),
        ], 'config');
    }
}
