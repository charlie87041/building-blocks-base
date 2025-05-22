<?php

return [
    'clean_code_rules_location' => base_path('nl_rules.txt'),

    'pipes' => [

        'routes' => [
            \App\Pipes\Routes\ExtractRouteDataPipe::class,
            \App\Pipes\Routes\ParseBindingsPipe::class,
            \App\Pipes\Routes\CrawlClassDependenciesPipe::class,
            App\Pipes\Routes\BuildExecutionGraphPipe::class,
            \App\Pipes\Routes\BuildOwlOntologyPipe::class,
            \App\Pipes\DebugPipe::class
        ],
        'routes_execution' => [
            App\Pipes\RoutesExecution\ExtractRouteExecutionDataPipe::class,
            App\Pipes\RoutesExecution\BuildUnifiedCodeFilePipe::class,
        ],
        'routes_matrix' => [
            \App\Pipes\RoutesMatrix\GenerateValidationMatrixPipe::class,
            \App\Pipes\RoutesMatrix\GenerateLogicMatrixPipe::class,
            \App\Pipes\RoutesMatrix\GenerateAuthMatrixPipe::class,
            \App\Pipes\RoutesMatrix\NormalizeTestMatrixPipe::class,
            \App\Pipes\RoutesMatrix\WriteTestMatrixToStoragePipe::class
        ],
        'tests' => [
            \App\Pipes\TestGeneration\GenerateTestFilePipe::class
        ],
        'refactor' => [
            \App\Pipes\CodeRefactor\GenerateCodeRefactorPipe::class,
            \App\Pipes\CodeRefactor\CodeRefactorRunHtmlPipe::class
        ],
        'docs' => [
            \App\Pipes\DocsGeneration\GenerateSwaggerSpecPipe::class,
            \App\Pipes\DocsGeneration\WriteSwaggerSpecToStoragePipe::class,
        ],
        'clean_code' => [
            \App\Pipes\CleanCode\GenerateDeptracRulesPipe::class,
            \App\Pipes\CleanCode\RunDeptracAnalysisPipe::class
        ]

    ],
    'parser' => \App\Support\Parser\SimplePhpFileParser::class,
    'categorizer' => \App\Support\Categorizer\SimpleHeuristicCategorizer::class,
    'graph_storage' => \App\Support\Storage\LocalGraphStorage::class,
    'output_directory' => storage_path('app/route-analysis'),

];
