<?php

namespace App\Console\Commands;

use App\Contexts\RouteCodeRefactorContext;
use App\Pipes\RoutesMatrix\TestMatrix;
use App\Support\Helpers;
use App\Support\Storage\GraphStorageFactory;
use App\Pipes\PipeBuilder;

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
