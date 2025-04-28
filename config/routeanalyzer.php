<?php

return [

    'pipes' => [

        'routes' => [
            \App\Pipes\Routes\ExtractRouteDataPipe::class,
            \App\Pipes\Routes\ParseBindingsPipe::class,
            \App\Pipes\Routes\CrawlClassDependenciesPipe::class,
            \App\Pipes\DebugPipe::class
        ],

    ],
    'parser' => \App\Support\Parser\SimplePhpFileParser::class,
    'output_directory' => storage_path('app/route-analysis'),

];
