<?php

namespace App\Support\Parser;

class SimplePhpFileParser implements PhpParserInterface
{
    public function parseNamespace(string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $lines = file($filePath);

        foreach ($lines as $line) {
            if (preg_match('/^namespace\s+(.+?);$/', trim($line), $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }
    public function parseUseStatements(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $uses = [];
        $seenAliases = [];

        $lines = file($filePath);

        foreach ($lines as $line) {
            $line = trim($line);

            if (str_starts_with($line, 'use')) {
                if (preg_match('/^use\s+(.+?)(?:\s+as\s+(.+?))?;$/', $line, $matches)) {
                    $full = trim($matches[1]);
                    $alias = isset($matches[2]) ? trim($matches[2]) : basename(str_replace('\\', '/', $matches[1]));

                    if (isset($seenAliases[$alias])) {
                        //TODO DO SOMETHING WITH ALIAS
                        logger()->warning("Alias collision detected: '{$alias}' in file {$filePath}");
                    } else {
                        $seenAliases[$alias] = true;
                        $uses[] = [
                            'full' => $full,
                            'alias' => $alias,
                        ];
                    }
                }
            }

            if (str_starts_with($line, 'class ') || str_starts_with($line, 'abstract class') || str_starts_with($line, 'final class')) {
                break;
            }
        }

        return $uses;
    }

    public function detectClassesUsedInMethod(\ReflectionMethod $method, string $namespace, array $useStatements): array
    {
        $fileName = $method->getFileName();
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();

        if (!$fileName || !file_exists($fileName)) {
            return [];
        }

        $lines = file($fileName);
        $methodCode = implode("\n", array_slice($lines, $startLine - 1, $endLine - $startLine + 1));

        $shortClassNames = [];

        // Detect "new SomeClass()"
        preg_match_all('/new\s+([\\\\A-Za-z0-9_]+)/', $methodCode, $newMatches);

        // Detect "app(SomeClass::class)" or "resolve(SomeClass::class)"
        preg_match_all('/(?:app|resolve)\(\s*([\\\\A-Za-z0-9_]+)::class/', $methodCode, $appMatches);

        // Detect "app()->make(SomeClass::class)", "$this->app->make(SomeClass::class)", "App::make(SomeClass::class)"
        preg_match_all('/(?:app\(\)|\$this->app|App)::make\(\s*([\\\\A-Za-z0-9_]+)::class/', $methodCode, $makeMatches);

        if (!empty($newMatches[1])) {
            foreach ($newMatches[1] as $match) {
                $shortClassNames[] = trim($match, '\\');
            }
        }

        if (!empty($appMatches[1])) {
            foreach ($appMatches[1] as $match) {
                $shortClassNames[] = trim($match, '\\');
            }
        }

        if (!empty($makeMatches[1])) {
            foreach ($makeMatches[1] as $match) {
                $shortClassNames[] = trim($match, '\\');
            }
        }

        // Resolve FQCN for all detected classes
        $fullyQualifiedClasses = [];

        foreach (array_unique($shortClassNames) as $shortName) {
            $fullyQualifiedClasses[] = $this->resolveFullyQualifiedClassName($shortName, $useStatements, $namespace);
        }

        return array_unique($fullyQualifiedClasses);
    }


    protected function resolveFullyQualifiedClassName(string $shortName, array $useStatements, string $namespace): string
    {
        foreach ($useStatements as $use) {
            if ($use['alias'] === $shortName) {
                return '\\' . ltrim($use['full'], '\\'); // Normalize FQCN
            }
        }

        return '\\' . rtrim($namespace, '\\') . '\\' . $shortName; // Normalize FQCN
    }
}
