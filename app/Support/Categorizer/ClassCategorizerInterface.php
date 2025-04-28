<?php

namespace App\Support\Categorizer;

interface ClassCategorizerInterface
{
    public function categorize(string $fqcn): string;
}
