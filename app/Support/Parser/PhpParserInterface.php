<?php

namespace App\Support\Parser;

interface PhpParserInterface
{
    public function parseNamespace(string $filePath): ?string;

    public function parseUseStatements(string $filePath): array;
    public function detectClassesUsedInMethod(\ReflectionMethod $method, string $namespace, array $useStatements): array;

}
