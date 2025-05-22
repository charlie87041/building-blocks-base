<?php

namespace BoostBrains\LaravelCodeCheck\Pipes;

use BoostBrains\LaravelCodeCheck\Contexts\BaseContext;

class DebugPipe
{
    public function handle(BaseContext $context, \Closure $next)
    {
        dump($context->properties);;

        return $next($context);
    }
}
