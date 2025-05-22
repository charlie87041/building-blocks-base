<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix;

use BoostBrains\LaravelCodeCheck\Contexts\RouteTestMatrixContext;
use BoostBrains\LaravelCodeCheck\Support\Helpers;
use BoostBrains\LaravelCodeCheck\Support\Storage\GraphStorageFactory;

class WriteTestMatrixToStoragePipe
{
    public function handle(RouteTestMatrixContext $context, \Closure $next)
    {
        $route = $context->getRoute();
        $normalizedRoute = Helpers::normalizeRouteToFileName($route['uri'] ?? 'unknown');
        $matrix = $context->getTestMatrix() ?? [];

        if (empty($matrix)) {
            logger()->warning("No se encontró matriz de pruebas para {$normalizedRoute}. Nada que guardar.");
            return $next($context);
        }

        $storage = GraphStorageFactory::make();

        foreach ($matrix as $flow => $scenarios) {
            if (!is_array($scenarios) || empty($scenarios)) {
                logger()->info("No hay escenarios para el flujo '{$flow}' en {$normalizedRoute}, se omite.");
                continue;
            }

            $path = "route-analysis/tests/matrix/{$normalizedRoute}.matrix.{$flow}.json";
            $storage->save($path, $scenarios);

            logger()->info("Matriz de pruebas escrita en: {$path} ({$flow}, " . count($scenarios) . " escenarios)");
        }

        return $next($context);
    }

}
