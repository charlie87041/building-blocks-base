<?php

namespace App\Pipes\CodeRefactor;

use App\Contexts\AppArchitectureContext;
use App\Contexts\RouteCodeRefactorContext;
use App\Contexts\RouteExecutionContext;
use App\Services\Commission\Commission;
use App\Services\PromptBuilder;
use App\Support\Helpers;
use App\Support\Storage\GraphStorageFactory;
use Symfony\Component\Yaml\Yaml;

class GenerateCodeRefactorPipe
{
    public function handle(RouteCodeRefactorContext $context, \Closure $next)
    {
        $code = $context->getExtractedCode();
        $route = $context->getRoute();
        $normalizedRoute = Helpers::normalizeRouteToFileName($route['uri'] ?? 'unknown');

        /** @var Commission $commission */
        $commission = app()->make(Commission::class);
        $prompt = PromptBuilder::forCodeRefactor($code);
        $refactor = $commission->getPrimaryExpert('code')->generateFromPrompt($prompt);
        $context->setRefactoringResults($refactor);
        $path = "refactor/{$normalizedRoute}.html";
        $storage = GraphStorageFactory::make();

        $storage->saveRaw($path,$refactor);

        return $next($context);
    }
}
