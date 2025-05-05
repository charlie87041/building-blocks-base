<?php

namespace App\Console\Commands;

use App\Support\Helpers;
use App\Support\Storage\GraphStorageFactory;
use Illuminate\Console\Command;
use App\Contexts\RouteTestMatrixContext;
use App\Pipes\PipeBuilder;
use function Laravel\Prompts\error;

class AnalyzeRouteTestMatrixCommand extends BaseRouteCommand
{
    protected $signature = 'bb:routes:test-matrix';
    protected $description = 'Analyze routes to generate suggested test matrices.';

    protected function buildContext($route, $controller, $method)
    {
        $storage = GraphStorageFactory::make();
        $normalizedRoute = Helpers::normalizeRouteToFileName($route->uri());
        $code = $storage->loadRaw("route-analysis/unified-code/{$normalizedRoute}.code.txt");
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
