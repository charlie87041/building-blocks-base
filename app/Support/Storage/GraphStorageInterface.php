<?php

namespace App\Support\Storage;

interface GraphStorageInterface
{
    public function save(string $path, array $graph): void;
    public function saveRaw(string $path, string $graph): void;

    public function load(string $path): ?array;
    public function loadRaw(string $path): ?string;
}
