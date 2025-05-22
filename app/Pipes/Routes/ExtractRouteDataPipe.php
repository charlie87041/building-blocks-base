<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\Routes;


use BoostBrains\LaravelCodeCheck\Contexts\RouteAnalysisContext;

class ExtractRouteDataPipe
{
    public function handle(RouteAnalysisContext $context, \Closure $next)
    {
        $route = $context->getRoute();

        if (!$route) {
            throw new \Exception("No route data available in context.");
        }

        $context->setRoute([
            'uri' => $route['uri'],
            'controller' => $route['controller'],
            'method' => $route['method'],
        ]);

        return $next($context);
    }
}
