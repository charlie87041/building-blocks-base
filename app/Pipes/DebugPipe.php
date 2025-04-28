<?php

namespace App\Pipes;

use App\Contexts\BaseContext;

class DebugPipe
{
    public function handle(BaseContext $context, \Closure $next)
    {
        dump($context->properties);;

        return $next($context);
    }
}
