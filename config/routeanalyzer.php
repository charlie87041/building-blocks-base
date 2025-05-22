<?php

return [
    'clean_code_rules_location' => base_path('nl_rules.txt'),

    'pipes' => [

        'routes' => [
            \BoostBrains\LaravelCodeCheck\Pipes\Routes\ExtractRouteDataPipe::class,
            \BoostBrains\LaravelCodeCheck\Pipes\Routes\ParseBindingsPipe::class,
            \BoostBrains\LaravelCodeCheck\Pipes\Routes\CrawlClassDependenciesPipe::class,
            BoostBrains\LaravelCodeCheck\Pipes\Routes\BuildExecutionGraphPipe::class,
            \BoostBrains\LaravelCodeCheck\Pipes\Routes\BuildOwlOntologyPipe::class,
            \BoostBrains\LaravelCodeCheck\Pipes\DebugPipe::class
        ],
        'routes_execution' => [
            BoostBrains\LaravelCodeCheck\Pipes\RoutesExecution\ExtractRouteExecutionDataPipe::class,
            BoostBrains\LaravelCodeCheck\Pipes\RoutesExecution\BuildUnifiedCodeFilePipe::class,
        ],
        'routes_matrix' => [
            \BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix\GenerateValidationMatrixPipe::class,
            \BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix\GenerateLogicMatrixPipe::class,
            \BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix\GenerateAuthMatrixPipe::class,
            \BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix\NormalizeTestMatrixPipe::class,
            \BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix\WriteTestMatrixToStoragePipe::class
        ],
        'tests' => [
            \BoostBrains\LaravelCodeCheck\Pipes\TestGeneration\GenerateTestFilePipe::class
        ],
        'docs' => [
            \BoostBrains\LaravelCodeCheck\Pipes\DocsGeneration\GenerateSwaggerSpecPipe::class,
            \BoostBrains\LaravelCodeCheck\Pipes\DocsGeneration\WriteSwaggerSpecToStoragePipe::class,
        ],
        'clean_code' => [
            \BoostBrains\LaravelCodeCheck\Pipes\CleanCode\GenerateDeptracRulesPipe::class,
            \BoostBrains\LaravelCodeCheck\Pipes\CleanCode\RunDeptracAnalysisPipe::class
        ]

    ],
    'parser' => \BoostBrains\LaravelCodeCheck\Support\Parser\SimplePhpFileParser::class,
    'categorizer' => \BoostBrains\LaravelCodeCheck\Support\Categorizer\SimpleHeuristicCategorizer::class,
    'graph_storage' => \BoostBrains\LaravelCodeCheck\Support\Storage\LocalGraphStorage::class,
    'output_directory' => storage_path('app/route-analysis'),

];
