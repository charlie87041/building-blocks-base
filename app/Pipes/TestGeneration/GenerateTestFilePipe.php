<?php

namespace App\Pipes\TestGeneration;

use App\Contexts\RouteTestMatrixContext;
use App\Services\Commission\Commission;
use App\Services\PromptBuilder;
use App\Support\Helpers;
use App\Support\Storage\GraphStorageFactory;

class GenerateTestFilePipe
{
    public function handle(RouteTestMatrixContext $context, \Closure $next)
    {
        $matrix = $context->getTestMatrix() ?? [];

        if (empty($matrix)) {
            logger()->warning("No hay matriz normalizada disponible para generar código de prueba.");
            return $next($context);
        }

        $route = $context->getRoute();
        $normalizedRoute = Helpers::normalizeRouteToFileName($route['uri'] ?? 'unknown');
        $format = config('llm.test_format', 'pest');

        $expert = (new Commission())->getPrimaryExpert(); // primer experto

        foreach ($matrix as $category => $scenarios) {
            if (!is_array($scenarios) || empty($scenarios)) {
                continue;
            }

            $tests = [];
            $storage = GraphStorageFactory::make();
            foreach ($scenarios as $scenario) {
                $prompt = PromptBuilder::forTestGeneration($route['uri'] ?? '/', $scenario, $format);
                $code = $expert->generateFromPrompt($prompt);

                if (!empty($code) && is_string($code)) {
                    $tests[] = $code;
                }
            }

            if (!empty($tests)) {
                $filename = "tests/generated/{$normalizedRoute}.{$category}.{$format}.php";
                $content =  implode("\n\n", $tests);
                $storage->saveRaw($filename, $content);
                logger()->info("Archivo generado para categoría '{$category}': {$filename}");
            }
        }

        return $next($context);
    }

}
