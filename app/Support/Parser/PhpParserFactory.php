<?php

namespace BoostBrains\LaravelCodeCheck\Support\Parser;

class PhpParserFactory
{
    public static function make(): PhpParserInterface
    {
        $parserClass = config('routeanalyzer.parser', SimplePhpFileParser::class);

        return new $parserClass();
    }
}
