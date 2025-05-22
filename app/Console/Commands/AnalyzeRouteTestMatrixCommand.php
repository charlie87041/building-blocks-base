<?php

namespace BoostBrains\LaravelCodeCheck\Console\Commands;

use BoostBrains\LaravelCodeCheck\Support\Helpers;
use BoostBrains\LaravelCodeCheck\Support\Storage\GraphStorageFactory;
use Illuminate\Console\Command;
use BoostBrains\LaravelCodeCheck\Contexts\RouteTestMatrixContext;
use BoostBrains\LaravelCodeCheck\Pipes\PipeBuilder;
use function Laravel\Prompts\error;

class AnalyzeRouteTestMatrixCommand extends BaseRouteCommand
{
    protected $signature = 'bb:routes:test-matrix';
    protected $description = 'Analyze routes to generate suggested test matrices.';

    protected function buildContext($route, $controller, $method)
    {
        $storage = GraphStorageFactory::make();
        $normalizedRoute = Helpers::normalizeRouteToFileName($route->uri());
        $code = $this->loadRouteCode($storage, $normalizedRoute);
        if (!$code){
            $this->error("NO code generated for $normalizedRoute");
            exit() ;
        }
        return new RouteTestMatrixContext([
            'route' => [
                'uri' => $route->uri(),
                'controller' => $controller,
                'method' => $method,
                'middlewares' => $route->gatherMiddleware()
            ],
            'extractedCode' => $code
        ]);
    }

    protected function getPipelineConfigKey(): string
    {
        return 'routes_matrix';
    }

    protected function getDescriptionLabel(): string
    {
        return 'Route Test Matrix Analysis';
    }

    protected function buildPipeline(array $pipes, $context)
    {
        return PipeBuilder::makeRouteMatrixBuilder($pipes, $context);
    }
}
