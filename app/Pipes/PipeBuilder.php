<?php

namespace BoostBrains\LaravelCodeCheck\Pipes;

use BoostBrains\LaravelCodeCheck\Contexts\AppArchitectureContext;
use BoostBrains\LaravelCodeCheck\Contexts\RouteAnalysisContext;
use BoostBrains\LaravelCodeCheck\Contexts\RouteExecutionContext;
use BoostBrains\LaravelCodeCheck\Contexts\RouteTestMatrixContext;
use BoostBrains\LaravelCodeCheck\Pipes\CleanCode\CleanCodePipeBuilder;
use BoostBrains\LaravelCodeCheck\Pipes\DocsGeneration\DocsPipeBuilder;
use BoostBrains\LaravelCodeCheck\Pipes\Routes\RouteAnalysisPipeBuilder;
use BoostBrains\LaravelCodeCheck\Pipes\RoutesExecution\RouteExecutionPipeBuilder;
use BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix\TestMatrixPipeBuilder;
use BoostBrains\LaravelCodeCheck\Pipes\TestGeneration\TestExecutionPipeBuilder;

class PipeBuilder
{
    public static function makeRouteBuilder(array $pipes, RouteAnalysisContext $context): PipeBuilderInterface
    {
        if ($context->requiresAsync) {
            throw new \Exception('Async pipeline not yet implemented.');
        }

        return new RouteAnalysisPipeBuilder($context, $pipes);
    }
    public static function makeRouteExecutionBuilder(array $pipes, RouteExecutionContext $context): PipeBuilderInterface
    {
        if ($context->requiresAsync) {
            throw new \Exception('Async pipeline not yet implemented.');
        }

        return new RouteExecutionPipeBuilder($context, $pipes);
    }
    public static function makeRouteMatrixBuilder(array $pipes, RouteTestMatrixContext $context): PipeBuilderInterface
    {
        if ($context->requiresAsync) {
            throw new \Exception('Async pipeline not yet implemented.');
        }

        return new TestMatrixPipeBuilder($context, $pipes);
    }
    public static function makeTestBuilder(array $pipes, RouteTestMatrixContext $context): PipeBuilderInterface
    {
        if ($context->requiresAsync) {
            throw new \Exception('Async pipeline not yet implemented.');
        }
        return new TestExecutionPipeBuilder($context, $pipes);
    }
    public static function makeSwaggerDocBuilder(array $pipes, RouteExecutionContext $context): PipeBuilderInterface
    {
        if ($context->requiresAsync) {
            throw new \Exception('Async pipeline not yet implemented.');
        }
        return new DocsPipeBuilder($context, $pipes);
    }
    public static function makeProjectArchitectureCheckerBuilder(array $pipes, AppArchitectureContext $context): PipeBuilderInterface
    {
        if ($context->requiresAsync) {
            throw new \Exception('Async pipeline not yet implemented.');
        }
        return new CleanCodePipeBuilder($context, $pipes);
    }
}
