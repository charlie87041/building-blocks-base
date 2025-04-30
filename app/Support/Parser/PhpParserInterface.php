<?php

namespace App\Support\Parser;

interface PhpParserInterface
{
    public function parseNamespace(string $filePath): ?string;

    public function parseUseStatements(string $filePath): array;
    public function detectClassesUsedInMethod(\ReflectionMethod $method, string $namespace, array $useStatements): array;


    public function extractMethodCalls(string $class, string $method): array; // [FQCN => [methods]]
    public function extractInjectedDependencies(string $class, string $method): array; // [FQCN, ...]
    public function detectFormRequestClasses(string $class, string $method): array; // [FQCN, ...]

}
