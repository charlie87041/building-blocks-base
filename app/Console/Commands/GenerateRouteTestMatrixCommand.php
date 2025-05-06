<?php

namespace App\Console\Commands;

use App\Pipes\RoutesMatrix\TestMatrix;
use App\Support\Helpers;
use App\Support\Storage\GraphStorageFactory;
use Illuminate\Console\Command;
use App\Contexts\RouteTestMatrixContext;
use App\Pipes\PipeBuilder;
use function Laravel\Prompts\error;

class GenerateRouteTestMatrixCommand extends AnalyzeRouteTestMatrixCommand
{
    protected $signature = 'bb:routes:test-make';
    protected $description = 'Generates test cases from test matrix.';

    protected function buildContext($route, $controller, $method)
    {
        $storage = GraphStorageFactory::make();
        $normalizedRoute = Helpers::normalizeRouteToFileName($route->uri());
        $baseUrl ="route-analysis/tests/matrix";
        $clazz = new \ReflectionClass(TestMatrix::class);
        $constants = $clazz->getConstants();
        $matrix = [];
        foreach ($constants as $key){
            $fullPath = "$baseUrl/$normalizedRoute.matrix.$key.json";
            $data = $storage->load($fullPath);
            if (empty($data))
                continue;
            $matrix[$key] = $data;
        }
        if (empty($matrix)){
            $this->error("NO matrix found for route $normalizedRoute");
            exit() ;
        }
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
            'testMatrix' => $matrix,
            'extractedCode' => $code
        ]);
    }


    protected function getPipelineConfigKey(): string
    {
        return 'tests';
    }

    protected function getDescriptionLabel(): string
    {
        return 'Route Test Generation';
    }

    protected function buildPipeline(array $pipes, $context)
    {
        return PipeBuilder::makeTestBuilder($pipes, $context);
    }

}
