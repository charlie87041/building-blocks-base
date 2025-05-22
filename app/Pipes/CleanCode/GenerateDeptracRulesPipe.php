<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\CleanCode;

use BoostBrains\LaravelCodeCheck\Contexts\AppArchitectureContext;
use BoostBrains\LaravelCodeCheck\Services\Commission\Commission;
use BoostBrains\LaravelCodeCheck\Services\PromptBuilder;
use BoostBrains\LaravelCodeCheck\Support\Storage\GraphStorageFactory;
use Symfony\Component\Yaml\Yaml;

class GenerateDeptracRulesPipe
{
    public function handle(AppArchitectureContext $context, \Closure $next)
    {
        $rulesDir = config('routeanalyzer.clean_code_rules_location');
        if (empty($rulesDir))
            throw new \Exception("rules file missing. Check routeanalyzer.clean_code_rules_location");

        $rulesText = file_get_contents($rulesDir);

        $prompt = PromptBuilder::forDeptracYaml($rulesText);
        /** @var Commission $commission */
        $commission = app()->make(Commission::class);

        $yaml = $commission->getPrimaryExpert()->generateFromPrompt($prompt);
        $path = 'architecture/deptrac.yaml';
        $storage = GraphStorageFactory::make();
        $storage->saveRaw($path, Yaml::dump($yaml, 4, 2));
        $context->setDeptracConfig($yaml);

        return $next($context);
    }
}
