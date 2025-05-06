<?php

namespace App\Pipes\DocsGeneration;

use App\Contexts\RouteExecutionContext;
use App\Support\Helpers;
use App\Support\Storage\GraphStorageFactory;

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
