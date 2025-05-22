<?php

namespace BoostBrains\LaravelCodeCheck\Support\Parser;

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

    public function extractMethodCalls(string $class, string $method): array
    {
        $result = [];

        if (!class_exists($class)) {
            return $result;
        }

        $reflection = new \ReflectionClass($class);
        if (!$reflection->hasMethod($method)) {
            return $result;
        }

        $methodCode = $this->getMethodSource($reflection->getMethod($method));
        $aliasMap = $this->parseUseStatements($reflection->getFileName());
        $instanceMap = $this->buildInstanceMap($reflection->getMethod($method), $methodCode, $aliasMap);

        if (preg_match_all('/\$(\w+)->(\w+)\s*\(/', $methodCode, $matches)) {
            foreach ($matches[1] as $i => $varName) {
                $calledMethod = $matches[2][$i];
                if (isset($instanceMap[$varName])) {
                    $fqcn = $instanceMap[$varName];
                    $result[$fqcn][] = $calledMethod;
                }
            }
        }

        return $result;
    }

    protected function getMethodSource(\ReflectionMethod $method): string
    {
        $file = $method->getFileName();
        $lines = file($file);

        return implode("", array_slice(
            $lines,
            $method->getStartLine() - 1,
            $method->getEndLine() - $method->getStartLine() + 1
        ));
    }


    protected function buildInstanceMap(\ReflectionMethod $method, string $methodCode, array $aliasMap): array
    {
        $instanceMap = [];

        // Inyecciones por tipo en la firma
        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            if ($type && !$type->isBuiltin()) {
                $fqcn = '\\' . ltrim($type->getName(), '\\');
                $instanceMap[$param->getName()] = $fqcn;
            }
        }

        // Instancias por "new"
        if (preg_match_all('/\$(\w+)\s*=\s*new\s+([\w\\\\]+)/', $methodCode, $matches)) {
            foreach ($matches[1] as $i => $varName) {
                $used = $matches[2][$i];
                $resolved = $aliasMap[$used] ?? $used;
                $instanceMap[$varName] = '\\' . ltrim($resolved, '\\');
            }
        }

        return $instanceMap;
    }


    public function extractInjectedDependencies(string $class, string $method): array
    {
        $result = [];

        if (!class_exists($class)) {
            return $result;
        }

        $reflection = new \ReflectionClass($class);
        if (!$reflection->hasMethod($method)) {
            return $result;
        }

        $params = $reflection->getMethod($method)->getParameters();

        foreach ($params as $param) {
            $type = $param->getType();
            if ($type && !$type->isBuiltin()) {
                $fqcn = '\\' . ltrim($type->getName(), '\\');
                $result[] = $fqcn;
            }
        }

        return $result;
    }
    public function detectFormRequestClasses(string $class, string $method): array
    {
        $formRequests = [];

        if (!class_exists($class)) {
            return [];
        }

        $reflection = new \ReflectionClass($class);
        if (!$reflection->hasMethod($method)) {
            return [];
        }

        foreach ($reflection->getMethod($method)->getParameters() as $param) {
            $type = $param->getType();
            if ($type && class_exists($type->getName())) {
                $paramClass = new \ReflectionClass($type->getName());
                if ($paramClass->isSubclassOf(\Illuminate\Foundation\Http\FormRequest::class)) {
                    $formRequests[] = $paramClass->getName();
                }
            }
        }

        return $formRequests;
    }

}
