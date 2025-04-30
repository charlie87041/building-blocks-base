<?php

namespace App\Pipes\Routes;

use App\Contexts\RouteAnalysisContext;
use App\Support\Categorizer\CategorizerFactory;
use App\Support\Storage\GraphStorageFactory;

class BuildExecutionGraphPipe
{
    protected array $nodes = [];
    protected array $edges = [];
    protected array $seenNodes = [];

    public function handle(RouteAnalysisContext $context, \Closure $next)
    {
        $classDependencyMap = $context->getClassDependencyMap();

        if (empty($classDependencyMap)) {
            throw new \Exception('No class dependency map available in context.');
        }

        $categorizer = CategorizerFactory::make();
        $storage = GraphStorageFactory::make();

        foreach ($classDependencyMap as $class => $data) {
            $this->addNode($class, $categorizer);

            foreach ($data['methods'] ?? [] as $method => $methodData) {
                foreach ($methodData['uses'] ?? [] as $usedClass) {
                    $this->addNode($usedClass, $categorizer);
                    $this->addEdge($class, $usedClass);
                }
            }
        }

        $graph = [
            'nodes' => array_values($this->nodes),
            'edges' => $this->edges,
        ];

        // Guardamos el grafo como JSON
        $storage->save('route-analysis/execution-graph.json', $graph);

        // Guardamos el classDependencyMap como JSON
        $storage->save('route-analysis/class-dependency-map.json', $classDependencyMap);

        return $next($context);
    }

    protected function addNode(string $fqcn, $categorizer)
    {
        $fqcn = '\\' . ltrim($fqcn, '\\');

        if (isset($this->seenNodes[$fqcn])) {
            return;
        }

        $this->nodes[$fqcn] = [
            'id' => $fqcn,
            'category' => $categorizer->categorize($fqcn),
        ];

        $this->seenNodes[$fqcn] = true;
    }

    protected function addEdge(string $from, string $to)
    {
        $this->edges[] = [
            'from' => '\\' . ltrim($from, '\\'),
            'to' => '\\' . ltrim($to, '\\'),
        ];
    }
}
