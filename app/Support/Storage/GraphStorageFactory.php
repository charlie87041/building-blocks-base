<?php

namespace App\Support\Storage;

class GraphStorageFactory
{
    public static function make(): GraphStorageInterface
    {
        $storageClass = config('routeanalyzer.graph_storage', LocalGraphStorage::class);

        return new $storageClass();
    }
}
