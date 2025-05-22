<?php

namespace BoostBrains\LaravelCodeCheck\Contexts;

use BoostBrains\LaravelCodeCheck\Contexts\BaseContext;

/**
 * @method array getRoute()
 * @method void setRoute(array $route)
 *
 * @method array getClassDependencyMap()
 * @method void setClassDependencyMap(array $map)
 *
 * @method string getExtractedCode()
 * @method void setExtractedCode(string $code)
 *
 * @method array getClassesToProcess()
 * @method void setClassesToProcess(array $classes)
 *
 * @method void setSwaggerSpec(?string $spec)
 * @method string getSwaggerSpec()
 */
class RouteExecutionContext extends BaseContext
{
}
