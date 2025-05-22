<?php

namespace BoostBrains\LaravelCodeCheck\Services\Commission;

use BoostBrains\LaravelCodeCheck\Services\Commission\Experts\LlmExpert;
use BoostBrains\LaravelCodeCheck\Services\PromptBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class Commission
{
    protected array $experts = [];
    protected  $focus;
    protected $selfReview;

    protected $role;

    public function __construct(string $focus = 'full', $role = '*')
    {
        $this->focus = $focus;
        $definitions = Config::get('llm.experts', []);
        $this->selfReview = config('llm.self_review_enabled', false);
        $this->role = $role;
        foreach ($definitions as $name => $config) {
            $class = $config['expert_class'] ?? LlmExpert::class;
            if ($this->hasRole($config, $role))
            $this->experts[$name] = new $class($name, $config, $this->selfReview);
        }
    }
    protected function hasRole(array $config, $role)
    {
        if ($role == '*')
            return true;
        $currentRoles = $config['roles'] ?? [];
        if ( is_array($currentRoles) )
            return in_array($role, $currentRoles) || in_array('*', $currentRoles) ;
        return $currentRoles == $role || $currentRoles == '*';
    }

    public function generateMatrix(string $code): array
    {
        $combined = [];

        foreach ($this->experts as $expert) {
            /** @var LlmExpert $expert */
            $scenarios = $expert->generate($code, $this->focus);

            foreach ($scenarios as $scenario) {
                $scenario['source'] = $expert->name;
                $combined[] = $scenario;
            }
        }

        return $combined;
    }

    public function normalizeMatrix(array $rawMatrix): array
    {
        if (empty($rawMatrix)) {
            return [];
        }
        /** @var LlmExpert $expert */
        $expert = $this->getPrimaryExpert();

        $prompt = $this->buildNormalizationPrompt($rawMatrix);

        return $expert->normalizeFromPrompt($prompt);
    }

    protected function buildNormalizationPrompt(array $matrix): string
    {
        $json = json_encode($matrix, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return PromptBuilder::normalizeTestMatrixPompt($json);
    }

    public function getPrimaryExpert(?string $role = '*'): ?LlmExpert
    {
        if (!$role || $role == '*')
            return reset($this->experts);
        $experts = array_filter($this->experts, fn($expert)=>$expert->hasRole($role));
        return  reset($experts);
    }
}
