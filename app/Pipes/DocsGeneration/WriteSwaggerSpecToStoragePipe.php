<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\DocsGeneration;

use BoostBrains\LaravelCodeCheck\Contexts\RouteExecutionContext;
use BoostBrains\LaravelCodeCheck\Support\Helpers;
use BoostBrains\LaravelCodeCheck\Support\Storage\GraphStorageFactory;

class WriteSwaggerSpecToStoragePipe
{
    public function handle(RouteExecutionContext $context, \Closure $next)
    {
        $swagger = $context->getSwaggerSpec();
        $route = $context->getRoute();
        $normalizedRoute = Helpers::normalizeRouteToFileName($route['uri'] ?? 'unknown');

        if (empty($swagger)) {
            logger()->warning("No hay Swagger para guardar.");
            return $next($context);
        }

        $storage = GraphStorageFactory::make();
        $filename = "docs/swagger/{$normalizedRoute}.swagger.json";
        $storage->saveRaw($filename, $swagger);

        logger()->info("Swagger guardado en: {$filename}");
        return $next($context);
    }

}
