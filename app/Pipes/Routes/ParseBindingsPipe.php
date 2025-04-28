<?php

namespace App\Pipes\Routes;

use App\Contexts\RouteAnalysisContext;
use Illuminate\Support\Facades\App;

class ParseBindingsPipe
{
    protected array $excludedNamespaces = [
        'Illuminate\\',
        'Laravel\\',
        'Psr\\',
        'Symfony\\',
    ];

    public function handle(RouteAnalysisContext $context, \Closure $next)
    {
        $bindings = [];

        foreach (App::getBindings() as $abstract => $details) {
            if ($this->shouldIncludeBinding($abstract, $details['concrete'])) {
                $bindings[$abstract] = $details['concrete'];
            }
        }

        $context->setBindings($bindings);

        return $next($context);
    }

    protected function shouldIncludeBinding($abstract, $concrete): bool
    {
        foreach ($this->excludedNamespaces as $prefix) {
            if (str_starts_with($abstract, $prefix) || (is_string($concrete) && str_starts_with($concrete, $prefix))) {
                return false;
            }
            // TODO closure bindings.
            if ($concrete instanceof \Closure) {
                return false;
            }
        }

        return true;
    }
}
