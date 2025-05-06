<?php

namespace App\Console\Commands;

use App\Contexts\RouteExecutionContext;
use App\Pipes\PipeBuilder;
use App\Support\Helpers;
use App\Support\Storage\GraphStorageFactory;
use Illuminate\Console\Command;

class RoutesDocSwaggerCommand extends BaseRouteCommand
{
    protected $signature = 'bb:routes:docs-swagger';
    protected $description = 'Attempts to generate swagger spec from generated file code.';

    protected function buildContext($route, $controller, $method)
    {
        $storage = GraphStorageFactory::make();
        $classDependencyMap = $this->loadClassDependencyMap($storage);

        $normalizedRoute = Helpers::normalizeRouteToFileName($route->uri());
        $code = $this->loadRouteCode($storage, $normalizedRoute);

        return new RouteExecutionContext([
            'route' => [
                'uri' => $route->uri(),
                'controller' => $controller,
                'method' => $method,
                'http_method' => current($route->methods()),
                'middlewares' => $route->gatherMiddleware(),
            ],
            'classDependencyMap' => $classDependencyMap,
            'extractedCode' => $code
        ]);
    }

    protected function getPipelineConfigKey(): string
    {
        return 'docs';
    }

    protected function getDescriptionLabel(): string
    {
        return 'Route Docs generation';
    }

    protected function buildPipeline(array $pipes, $context)
    {
        return PipeBuilder::makeSwaggerDocBuilder($pipes, $context);
    }
}
