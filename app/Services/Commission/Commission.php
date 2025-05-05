<?php

namespace App\Services\Commission;

use App\Services\Commission\Experts\LlmExpert;
use App\Services\PromptBuilder;
use Illuminate\Support\Facades\Config;

class Commission
{
    protected array $experts = [];
    protected  $focus;
    protected $selfReview;

    public function __construct(string $focus = 'full')
    {
        $this->focus = $focus;
        $definitions = Config::get('llm.experts', []);
        $this->selfReview = config('llm.self_review_enabled', false);

        foreach ($definitions as $name => $config) {
            $class = $config['expert_class'] ?? LlmExpert::class;
            $this->experts[$name] = new $class($name, $config, $this->selfReview);
        }
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

    public function getPrimaryExpert(): ?LlmExpert
    {
        return reset($this->experts);
    }
}
