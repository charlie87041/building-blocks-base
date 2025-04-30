<?php

namespace App\Console\Commands;

use App\Contexts\RouteAnalysisContext;
use App\Pipes\PipeBuilder;

class AnalyzeRoutesCommand extends BaseRouteCommand
{
    protected $signature = 'bb:routes';
    protected $description = 'Analyze application routes for architecture and dependencies.';

    protected function buildContext($route, $controller, $method)
    {
        return new RouteAnalysisContext([
            'route' => [
                'uri' => $route->uri(),
                'controller' => $controller,
                'method' => $method,
                'middlewares' => $route->gatherMiddleware(),
            ]
        ]);
    }

    protected function getPipelineConfigKey(): string
    {
        return 'routes';
    }

    protected function getDescriptionLabel(): string
    {
        return 'Route Architecture Analysis';
    }

    protected function buildPipeline(array $pipes, $context)
    {
        return PipeBuilder::makeRouteBuilder($pipes, $context);
    }
}
