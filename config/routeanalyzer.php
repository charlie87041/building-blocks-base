<?php

return [

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

    ],
    'parser' => \App\Support\Parser\SimplePhpFileParser::class,
    'categorizer' => \App\Support\Categorizer\SimpleHeuristicCategorizer::class,
    'graph_storage' => \App\Support\Storage\LocalGraphStorage::class,
    'output_directory' => storage_path('app/route-analysis'),

];
