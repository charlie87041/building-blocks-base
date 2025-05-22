<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\CodeRefactor;

use BoostBrains\LaravelCodeCheck\Contexts\AppArchitectureContext;
use BoostBrains\LaravelCodeCheck\Contexts\RouteCodeRefactorContext;
use BoostBrains\LaravelCodeCheck\Contexts\RouteExecutionContext;
use BoostBrains\LaravelCodeCheck\Services\Commission\Commission;
use BoostBrains\LaravelCodeCheck\Services\PromptBuilder;
use BoostBrains\LaravelCodeCheck\Support\Helpers;
use BoostBrains\LaravelCodeCheck\Support\Storage\GraphStorageFactory;
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
