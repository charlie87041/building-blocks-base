<?php

namespace App\Pipes\Routes;


use App\Contexts\RouteAnalysisContext;

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
