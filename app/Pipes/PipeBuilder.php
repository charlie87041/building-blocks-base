<?php

namespace App\Pipes;

use App\Contexts\RouteAnalysisContext;
use App\Pipes\Routes\RouteAnalysisPipeBuilder;

class PipeBuilder
{
    public static function makeRouteBuilder(array $pipes, RouteAnalysisContext $context): PipeBuilderInterface
    {
        if ($context->requiresAsync) {
            throw new \Exception('Async pipeline not yet implemented.');
        }

        return new RouteAnalysisPipeBuilder($context, $pipes);
    }
}
