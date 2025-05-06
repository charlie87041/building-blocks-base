<?php

namespace App\Pipes\RoutesExecution;

use App\Contexts\RouteExecutionContext;
use App\Support\Helpers;
use App\Support\Storage\GraphStorageFactory;
use App\Support\Storage\GraphStorageInterface;
use ReflectionClass;
use ReflectionMethod;
use Illuminate\Support\Facades\Log;

class BuildUnifiedCodeFilePipe
{
    public function handle(RouteExecutionContext $context, \Closure $next)
    {
        $storage = GraphStorageFactory::make();
        $route = $context->getRoute();
        $classesToProcess = $context->getClassesToProcess();

        if (empty($route) || empty($classesToProcess)) {
            throw new \Exception('Missing route or classes to process.');
        }
        $output = [];
        foreach ($classesToProcess as $className => $details) {
            if (!class_exists($className)) {
                Log::warning("Class not found while building unified file: $className");
                continue;
            }

            $reflection = new ReflectionClass($className);
            $namespace = $reflection->getNamespaceName();
            $type = $details['type'] ?? 'Unknown';
            $methods = $details['methods'] ?? [];

            $classCode = $this->extractRelevantMethods($reflection, $methods);

            $output[] = "// ==== START CLASS: " . class_basename($className) . " (namespace: $namespace, category: $type) ====";
            $output[] = $classCode;
            $output[] = "// ==== END CLASS: " . class_basename($className) . " ====";
            $output[] = ""; // Espacio extra entre bloques
        }

        if (empty($output)) {
            Log::warning("No code extracted for route: " . ($route['uri'] ?? 'unknown'));
            return $next($context);
        }

        $normalizedRoute = Helpers::normalizeRouteToFileName($route['uri'] ?? 'unknown');
        $storage->save("route-analysis/unified-code/{$normalizedRoute}.code.txt", ['raw' => implode("\n", $output)]);
        $this->saveIndexFile($storage, $route, $output);
        $context->setExtractedCode(implode("\n", $output));
        return $next($context);
    }

    protected function extractRelevantMethods(ReflectionClass $reflection, array $methodNames): string
    {
        $lines = file($reflection->getFileName());
        $classCode = "class " . $reflection->getShortName() . "\n{\n";

        foreach ($methodNames as $methodName) {
            if (!$reflection->hasMethod($methodName)) {
                Log::warning("Method $methodName not found in " . $reflection->getName());
                continue;
            }

            $method = $reflection->getMethod($methodName);
            $classCode .= $this->extractMethodCode($method, $lines) . "\n";
        }

        $classCode .= "}\n";

        return $classCode;
    }

    protected function extractMethodCode(ReflectionMethod $method, array $lines): string
    {
        $start = $method->getStartLine() - 1;
        $end = $method->getEndLine() - 1;

        $codeLines = array_slice($lines, $start, $end - $start + 1);

        $joined = implode("", $codeLines);
        $openCount = substr_count($joined, '{');
        $closeCount = substr_count($joined, '}');

        $i = 1;
        while ($openCount > $closeCount && isset($lines[$end + $i])) {
            $codeLines[] = $lines[$end + $i];
            $joined = implode("", $codeLines);
            $closeCount = substr_count($joined, '}');
            $i++;
        }

        return implode("", $codeLines);
    }


    protected function saveIndexFile(GraphStorageInterface $storage, array $route, array $output)
    {
        $normalizedRoute = Helpers::normalizeRouteToFileName($route['uri'] ?? 'unknown');
        $filename = "{$normalizedRoute}.code.txt";
        $storage->save("route-analysis/unified-code/{$filename}", ['raw' => implode("\n", $output)]);

        $indexPath = 'route-analysis/routes-index.json';
        $index = $storage->load($indexPath) ?? [];

        $index[] = [
            'uri' => $route['uri'],
            'controller' => $route['controller'],
            'method' => $route['method'],
            'code_file' => $filename,
        ];

        $storage->save($indexPath, $index);
    }
}
