<?php

namespace App\Pipes\CleanCode;

use App\Contexts\AppArchitectureContext;
use App\Services\Commission\Commission;
use App\Services\PromptBuilder;
use App\Support\Storage\GraphStorageFactory;

class GenerateDeptracRulesPipe
{
    public function handle(AppArchitectureContext $context, \Closure $next)
    {
        $rulesDir = config('routeanalyzer.clean_code_rules_location');
        if (empty($rulesDir))
            throw new \Exception("rules file missing. Check routeanalyzer.clean_code_rules_location");

        $rulesText = file_get_contents($rulesDir);

        $prompt = PromptBuilder::forDeptracYaml($rulesText);
        dd($prompt);
        /** @var Commission $commission */
        $commission = app()->make(Commission::class);

        $yaml = $commission->getPrimaryExpert()->generateFromPrompt($prompt);
        $path = 'architecture/deptrac.yaml';
        $storage = GraphStorageFactory::make();
        $storage->saveRaw($path, $yaml);
        $context->setDeptracConfig($yaml);

        return $next($context);
    }
}
