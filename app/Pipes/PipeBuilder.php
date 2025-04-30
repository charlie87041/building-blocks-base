<?php

namespace App\Pipes;

use App\Contexts\RouteAnalysisContext;
use App\Contexts\RouteExecutionContext;
use App\Pipes\Routes\RouteAnalysisPipeBuilder;
use App\Pipes\RoutesExecution\RouteExecutionPipeBuilder;

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
}
