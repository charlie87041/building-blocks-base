<?php

namespace BoostBrains\LaravelCodeCheck\Support\Categorizer;

interface ClassCategorizerInterface
{
    public function categorize(string $fqcn): string;
}
