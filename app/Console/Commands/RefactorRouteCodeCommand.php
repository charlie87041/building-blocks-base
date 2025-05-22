<?php

namespace BoostBrains\LaravelCodeCheck\Console\Commands;

use BoostBrains\LaravelCodeCheck\Console\Commands\AnalyzeRouteTestMatrixCommand;
use BoostBrains\LaravelCodeCheck\Contexts\RouteCodeRefactorContext;
use BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix\TestMatrix;
use BoostBrains\LaravelCodeCheck\Support\Helpers;
use BoostBrains\LaravelCodeCheck\Support\Storage\GraphStorageFactory;
use BoostBrains\LaravelCodeCheck\Pipes\PipeBuilder;

class RefactorRouteCodeCommand extends AnalyzeRouteTestMatrixCommand
{
    protected $signature = 'bb:routes:refactor';
    protected $description = 'Refactors code with IA agent';

    protected function buildContext($route, $controller, $method)
    {
        $storage = GraphStorageFactory::make();
        $normalizedRoute = Helpers::normalizeRouteToFileName($route->uri());
        $code = $this->loadRouteCode($storage, $normalizedRoute);
        if (!$code){
            $this->error("NO code generated for $normalizedRoute");
            exit() ;
        }
        return new RouteCodeRefactorContext([
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
        return 'refactor';
    }

    protected function getDescriptionLabel(): string
    {
        return 'Route Code refactor';
    }

    protected function buildPipeline(array $pipes, $context)
    {
        return PipeBuilder::makeRouteCodeRefactorBuilder($pipes, $context);
    }

}
