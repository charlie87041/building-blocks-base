<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix;

use BoostBrains\LaravelCodeCheck\Contexts\RouteTestMatrixContext;
use BoostBrains\LaravelCodeCheck\Services\Commission\Commission;

class NormalizeTestMatrixPipe
{
    public function handle(RouteTestMatrixContext $context, \Closure $next)
    {
        $matrix = $context->getTestMatrix() ?? [];

        if (empty($matrix)) {
            logger()->warning("No hay matriz de pruebas para normalizar.");
            return $next($context);
        }

        $flat = [];

        foreach ($matrix as $segment) {
            if (is_array($segment)) {
                $flat = array_merge($flat, $segment);
            }
        }

        $commission = new Commission(); // usa el primer experto
        $normalized = $commission->normalizeMatrix($flat);

        if (!empty($normalized)) {
            $context->setTestMatrix(['full' => $normalized]);
            logger()->info("Matriz normalizada con éxito, lista para escritura final.");
        } else {
            logger()->warning("La normalización no devolvió resultados válidos.");
        }

        return $next($context);
    }
}
