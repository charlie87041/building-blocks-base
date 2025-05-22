<?php

namespace BoostBrains\LaravelCodeCheck\Support;

class Helpers
{
    public static function normalizeRouteToFileName(string $uri): string
    {
        $uri = str_replace(['/', '{', '}'], ['-', '', ''], $uri);
        return strtolower(trim($uri, '-'));
    }
}
