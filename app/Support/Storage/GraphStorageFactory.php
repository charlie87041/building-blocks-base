<?php

namespace BoostBrains\LaravelCodeCheck\Support\Storage;

class GraphStorageFactory
{
    public static function make(): GraphStorageInterface
    {
        $storageClass = config('routeanalyzer.graph_storage', LocalGraphStorage::class);

        return new $storageClass();
    }
}
