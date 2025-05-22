<?php

namespace BoostBrains\LaravelCodeCheck\Support\Categorizer;

class SimpleHeuristicCategorizer implements ClassCategorizerInterface
{
    protected array $manualCategories;

    public function __construct()
    {
        $this->manualCategories = config('routeanalyzer.categories', []);
    }

    public function categorize(string $fqcn): string
    {
        if (isset($this->manualCategories[$fqcn])) {
            return $this->manualCategories[$fqcn];
        }

        if (str_contains($fqcn, '\\Http\\Controllers\\')) {
            return 'Controller';
        }
        if (str_contains($fqcn, '\\Repositories\\')) {
            return 'Repository';
        }
        if (str_contains($fqcn, '\\Services\\')) {
            return 'Service';
        }
        if (str_contains($fqcn, '\\Models\\')) {
            return 'Model';
        }
        if (str_contains($fqcn, '\\Policies\\')) {
            return 'Policy';
        }
        if (str_contains($fqcn, '\\External\\') || str_contains($fqcn, '\\ThirdParty\\')) {
            return 'ExternalClient';
        }

        if (str_ends_with($fqcn, 'Controller')) {
            return 'Controller';
        }
        if (str_ends_with($fqcn, 'Repository')) {
            return 'Repository';
        }
        if (str_ends_with($fqcn, 'Service')) {
            return 'Service';
        }
        if (str_ends_with($fqcn, 'Policy')) {
            return 'Policy';
        }

        return 'Unknown';
    }
}
