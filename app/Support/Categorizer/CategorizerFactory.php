<?php

namespace App\Support\Categorizer;

class CategorizerFactory
{
    public static function make(): ClassCategorizerInterface
    {
        $categorizerClass = config('routeanalyzer.categorizer', SimpleHeuristicCategorizer::class);

        return new $categorizerClass();
    }
}
