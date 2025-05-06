<?php

namespace App\Pipes;

use App\Contexts\RouteAnalysisContext;
use App\Contexts\RouteExecutionContext;
use App\Contexts\RouteTestMatrixContext;
use App\Pipes\DocsGeneration\DocsPipeBuilder;
use App\Pipes\Routes\RouteAnalysisPipeBuilder;
use App\Pipes\RoutesExecution\RouteExecutionPipeBuilder;
use App\Pipes\RoutesMatrix\TestMatrixPipeBuilder;
use App\Pipes\TestGeneration\TestExecutionPipeBuilder;

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
}
