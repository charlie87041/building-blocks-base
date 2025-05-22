<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix;

use BoostBrains\LaravelCodeCheck\Contexts\RouteTestMatrixContext;
use BoostBrains\LaravelCodeCheck\Services\Commission\Commission;
use BoostBrains\LaravelCodeCheck\Support\Helpers;

abstract class TestMatrix
{
    const VALIDATION_FLOW = 'validation';
    const LOGIC_FLOW = 'logic';
    const AUTH_FLOW = 'auth';
    public function handle(RouteTestMatrixContext $context, \Closure $next)
    {

        $route = $context->getRoute();
        $normalizedRoute = Helpers::normalizeRouteToFileName($route['uri'] ?? 'unknown');
        $flow = $this->getFlow();$code = $context->getExtractedCode();
        if (empty(trim($code))) {
            logger()->warning("Código vacío para la ruta {$normalizedRoute} en etapa '{$flow}'.");
            return $next($context);
        }
        $commission = new Commission($flow);
        $scenarios = $commission->generateMatrix($code);

        if (!empty($scenarios)) {
            $matrix = $context->getTestMatrix() ?? [];
            $matrix[$flow] = array_merge($matrix[$flow] ?? [], $scenarios);
            $context->setTestMatrix($matrix);
            logger()->info("Escenarios de tipo $flow agregados al contexto.");
        } else {
            logger()->error("No se generó ninguna matriz para {$normalizedRoute}");
        }

        return $next($context);
    }

    protected abstract function getFlow();



}
