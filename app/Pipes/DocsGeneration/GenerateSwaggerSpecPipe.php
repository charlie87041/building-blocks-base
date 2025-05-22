<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\DocsGeneration;

use BoostBrains\LaravelCodeCheck\Contexts\RouteExecutionContext;
use BoostBrains\LaravelCodeCheck\Services\Commission\Commission;
use BoostBrains\LaravelCodeCheck\Services\PromptBuilder;

class GenerateSwaggerSpecPipe
{
    public function handle(RouteExecutionContext $context, \Closure $next)
    {
        $code = $context->getExtractedCode();
        $url = $context->getRoute()['uri'] ?? '/unknown';
        $method = $context->getRoute()['http_method'];
        $uri = "$method: $url";
        if (empty($code)) {
            logger()->warning("No se encontró código consolidado para generar Swagger.");
            return $next($context);
        }

        $prompt = PromptBuilder::forSwaggerSpecGeneration($code, $uri);
        $expert = (new Commission())->getPrimaryExpert('docs');
        $swagger = $expert->generateFromPrompt($prompt);

        $context->setSwaggerSpec($swagger);
        logger()->info("Swagger generado con éxito para {$uri}");

        return $next($context);
    }


}
