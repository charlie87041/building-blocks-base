<?php

namespace App\Pipes\Routes;

use App\Contexts\RouteAnalysisContext;
use App\Support\Parser\PhpParserFactory;
use App\Support\Parser\PhpParserInterface;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionMethod;

class CrawlClassDependenciesPipe
{
    protected array $visited = [];
    protected PhpParserInterface $parser;

    public function __construct(PhpParserInterface $parser = null)
    {
        $this->parser = $parser ?? PhpParserFactory::make();
    }

    public function handle(RouteAnalysisContext $context, \Closure $next)
    {
        $this->visited = []; // Reset per route

        $route = $context->getRoute();
        $bindings = $context->getBindings();

        $map = [];

        if (!$route) {
            throw new \Exception('No route information available in context.');
        }

        $controllerClass = $route['controller'];
        $method = $route['method'];

        $this->crawlMethod($controllerClass, $method, $bindings, $map);

        $context->setClassDependencyMap($map);

        return $next($context);
    }

    protected function crawlMethod(string $className, string $methodName, array $bindings, array &$map)
    {
        $signature = $className . '@' . $methodName;

        if (in_array($signature, $this->visited)) {
            return; // Avoid infinite recursion
        }

        $this->visited[] = $signature;

        if (!class_exists($className)) {
            return;
        }

        $reflection = new ReflectionClass($className);

        if (!$reflection->hasMethod($methodName)) {
            return;
        }

        $method = $reflection->getMethod($methodName);

        $namespace = $this->parser->parseNamespace($method->getFileName());
        $useStatements = $this->parser->parseUseStatements($method->getFileName());
        $usedClasses = $this->parser->detectClassesUsedInMethod($method, $namespace, $useStatements);

        foreach ($usedClasses as $usedClass) {
            $normalizedClass = $this->normalizeFqcn($usedClass);
            $map[$className]['methods'][$methodName]['uses'][] = $normalizedClass;
            $this->crawlClass($normalizedClass, $bindings, $map);
        }
    }

    protected function crawlClass(string $className, array $bindings, array &$map)
    {
        if (interface_exists($className) && isset($bindings[$className])) {
            $className = $bindings[$className];
        }

        if (!class_exists($className)) {
            return;
        }

        $reflection = new ReflectionClass($className);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== $className) {
                continue;
            }

            $this->crawlMethod($className, $method->getName(), $bindings, $map);
        }
    }

    protected function normalizeFqcn(string $fqcn): string
    {
        return '\\' . ltrim($fqcn, '\\');
    }
}
