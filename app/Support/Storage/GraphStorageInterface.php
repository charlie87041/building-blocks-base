<?php

namespace App\Support\Storage;

interface GraphStorageInterface
{
    public function save(string $path, array $graph): void;

    public function load(string $path): ?array;
}
