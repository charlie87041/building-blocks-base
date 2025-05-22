<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\Routes;

use BoostBrains\LaravelCodeCheck\Contexts\RouteAnalysisContext;
use BoostBrains\LaravelCodeCheck\Support\Storage\GraphStorageFactory;

class BuildOwlOntologyPipe
{
    protected string $baseUri;

    public function __construct()
    {
        $this->baseUri = config('routeanalyzer.owl_base_uri', 'http://yourdomain.com/ontology#');
    }

    public function handle(RouteAnalysisContext $context, \Closure $next)
    {
        $storage = GraphStorageFactory::make();
        $graph = $storage->load('route-analysis/execution-graph.json');
        if (empty($graph)) {
            throw new \Exception('No execution graph available to generate OWL.');
        }

        $xml = $this->buildOntology($graph);
        // Guardamos OWL como texto plano
        $storage->save('route-analysis/execution-ontology.owl', ['raw' => $xml]);

        return $next($context);
    }

    protected function buildOntology(array $graph): string
    {
        $xml = [];
        $xml[] = '<?xml version="1.0"?>';
        $xml[] = '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"';
        $xml[] = '         xmlns:owl="http://www.w3.org/2002/07/owl#"';
        $xml[] = '         xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"';
        $xml[] = '         xmlns="' . $this->baseUri . '">';
        $xml[] = '';

        $xml[] = '    <owl:Ontology rdf:about="' . $this->baseUri . '"/>';
        $xml[] = '';

        // 1. Definimos todas las categorías como owl:Class
        $categories = [];

        foreach ($graph['nodes'] ?? [] as $node) {
            $categories[$node['category']] = true;
        }

        foreach (array_keys($categories) as $category) {
            $xml[] = '    <owl:Class rdf:about="#' . htmlspecialchars($category) . '"/>';
        }

        $xml[] = '';

        // 2. Definimos las clases (nodos) como subClassOf su categoría
        foreach ($graph['nodes'] ?? [] as $node) {
            $classId = $this->extractSimpleName($node['id']);
            $category = $node['category'] ?? 'Unknown';

            $xml[] = '    <owl:Class rdf:about="#' . htmlspecialchars($classId) . '">';
            $xml[] = '        <rdfs:subClassOf rdf:resource="#' . htmlspecialchars($category) . '"/>';
            $xml[] = '    </owl:Class>';
        }

        $xml[] = '';

        // 3. Definimos las relaciones (edges) como ObjectProperties
        foreach ($graph['edges'] ?? [] as $edge) {
            $fromId = $this->extractSimpleName($edge['from']);
            $toId = $this->extractSimpleName($edge['to']);

            $xml[] = '    <owl:ObjectProperty rdf:about="#uses">';
            $xml[] = '        <rdfs:domain rdf:resource="#' . htmlspecialchars($fromId) . '"/>';
            $xml[] = '        <rdfs:range rdf:resource="#' . htmlspecialchars($toId) . '"/>';
            $xml[] = '    </owl:ObjectProperty>';
        }

        $xml[] = '</rdf:RDF>';

        return implode("\n", $xml);
    }

    protected function extractSimpleName(string $fqcn): string
    {
        return basename(str_replace('\\', '/', $fqcn));
    }
}
