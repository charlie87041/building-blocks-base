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
