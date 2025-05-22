<?php

namespace BoostBrains\LaravelCodeCheck\Console\Commands;

use BoostBrains\LaravelCodeCheck\Contexts\RouteExecutionContext;
use BoostBrains\LaravelCodeCheck\Pipes\PipeBuilder;
use BoostBrains\LaravelCodeCheck\Support\Storage\GraphStorageFactory;
use Illuminate\Console\Command;

class RoutesCodeCommand extends BaseRouteCommand
{
    protected $signature = 'bb:routes:code';
    protected $description = 'Analyze application routes and generate unified code context for each route.';

    protected function buildContext($route, $controller, $method)
    {
        $storage = GraphStorageFactory::make();
        $classDependencyMap = $this->loadClassDependencyMap($storage);
        if (empty($classDependencyMap)) {
            $this->error('Missing class-dependency-map.json. Please run "analyze:routes" first.');
            return Command::FAILURE;
        }
        return new RouteExecutionContext([
            'route' => [
                'uri' => $route->uri(),
                'controller' => $controller,
                'method' => $method,
                'middlewares' => $route->gatherMiddleware(),
            ],
            'classDependencyMap' => $classDependencyMap,
        ]);
    }

    protected function getPipelineConfigKey(): string
    {
        return 'routes_execution';
    }

    protected function getDescriptionLabel(): string
    {
        return 'Route Execution Context Analysis';
    }

    protected function buildPipeline(array $pipes, $context)
    {
        return PipeBuilder::makeRouteExecutionBuilder($pipes, $context);
    }
}
