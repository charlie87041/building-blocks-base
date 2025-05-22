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

class
CodeRefactorRunHtmlPipe
{
    public function handle(RouteCodeRefactorContext $context, \Closure $next)
    {
        $route = $context->getRoute();
        $normalizedRoute = Helpers::normalizeRouteToFileName($route['uri'] ?? 'unknown');

        $path = "refactor/{$normalizedRoute}.html";
        $storage = GraphStorageFactory::make();
        $data = $storage->path($path);

        // Detectar sistema operativo y abrir
        if (PHP_OS_FAMILY === 'Windows') {
            exec("start \"\" \"$data\"");
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            exec("open \"$data\"");
        } else {
            exec("xdg-open \"$data\"");
        }
        return $next($context);
    }
}
