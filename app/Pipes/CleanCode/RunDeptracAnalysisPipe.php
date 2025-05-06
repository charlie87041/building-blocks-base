<?php

namespace App\Pipes\CleanCode;

use App\Contexts\AppArchitectureContext;
use App\Support\Storage\GraphStorageFactory;

class RunDeptracAnalysisPipe
{
    public function handle(AppArchitectureContext $context, \Closure $next)
    {
        $storage = GraphStorageFactory::make();
        $output = $storage->path("architecture\\violations\\app.json");
        $yamlPath = $storage->path('architecture\deptrac.yaml');
        $command = "php vendor/bin/deptrac analyse --config-file={$yamlPath} --formatter=json > {$output}";

        exec($command);
        $violations = json_decode(file_get_contents($output), true) ;
        logger($output);
        $context->setViolations($violations);

        return $next($context);
    }
}
