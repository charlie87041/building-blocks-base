<?php

namespace App\Pipes\RoutesExecution;

use App\Contexts\RouteExecutionContext;
use App\Support\Parser\PhpParserFactory;
use App\Support\Parser\PhpParserInterface;
use ReflectionClass;
use Illuminate\Support\Facades\Log;

class ExtractRouteExecutionDataPipe
{

    protected PhpParserInterface $parser;

    public function __construct(PhpParserInterface $parser = null)
    {
        $this->parser = $parser ?? PhpParserFactory::make();
    }
    public function handle(RouteExecutionContext $context, \Closure $next)
    {
        $route = $context->getRoute();
        $classDependencyMap = $context->getClassDependencyMap();

        if (empty($route) || empty($classDependencyMap)) {
            throw new \Exception('Missing route or dependency information.');
        }

        $controllerClass = $route['controller'];
        $controllerMethod = $route['method'];

        $classes = [
            $controllerClass => [
                'methods' => [$controllerMethod],
                'type' => 'Controller',
            ]
        ];

        foreach ($this->parser->extractMethodCalls($controllerClass, $controllerMethod) as $depClass => $methods) {
            foreach ($methods as $methodName) {
                $classDependencyMap[$controllerClass]['methods'][$controllerMethod]['method_calls'][$depClass][] = $methodName;
            }
        }

        foreach ($this->parser->extractInjectedDependencies($controllerClass, $controllerMethod) as $depClass) {
            $classDependencyMap[$controllerClass]['methods'][$controllerMethod]['uses'][] = $depClass;
        }

        $this->expandDependencies($controllerClass, $controllerMethod, $classes, $classDependencyMap);

        foreach ($route['middlewares'] ?? [] as $middlewareClass) {
            if (class_exists($middlewareClass)) {
                $classes[$middlewareClass] = [
                    'methods' => ['handle'],
                    'type' => 'Middleware',
                ];
            }
        }

        foreach ($this->parser->detectFormRequestClasses($controllerClass, $controllerMethod) as $formRequestClass) {
            $classes[$formRequestClass] = [
                'methods' => ['rules', 'authorize'],
                'type' => 'FormRequest',
            ];
        }

        $context->setClassesToProcess($classes);

        return $next($context);
    }


    protected function expandDependencies(string $class, string $method, array &$classesToProcess, array $classDependencyMap)
    {

        if (!isset($classDependencyMap[$class]['methods'][$method])) {
            return;
        }
        $methodData = $classDependencyMap[$class]['methods'][$method];
        // Expandimos clases usadas (uses)
        if (isset($methodData['uses'])) {
            foreach ($methodData['uses'] as $usedClass) {
                if (!isset($classesToProcess[$usedClass])) {
                    $classesToProcess[$usedClass] = [
                        'methods' => [], // Agregaremos métodos relevantes luego
                        'type' => 'Dependency',
                    ];
                }
                // Expandimos métodos usados explícitamente si tenemos method_calls
                if (isset($methodData['method_calls'][$usedClass])) {
                    $classesToProcess[$usedClass]['methods'] = array_merge(
                        $classesToProcess[$usedClass]['methods'],
                        $methodData['method_calls'][$usedClass]
                    );
                } else {
                    // Fallback: si no sabemos qué métodos, agregar todos los métodos de la clase
                    $classesToProcess[$usedClass]['methods'] = array_merge(
                        $classesToProcess[$usedClass]['methods'],
                        array_keys($classDependencyMap[$usedClass]['methods'] ?? [])
                    );
                }

                // Llamada recursiva para expandir sub-dependencias
                foreach ($classesToProcess[$usedClass]['methods'] as $depMethod) {
                    $this->expandDependencies($usedClass, $depMethod, $classesToProcess, $classDependencyMap);
                }
            }
        }
    }

}
