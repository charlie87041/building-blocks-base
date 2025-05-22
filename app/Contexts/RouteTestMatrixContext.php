<?php

namespace BoostBrains\LaravelCodeCheck\Contexts;

use Coreinvent\Component\Pipeline\Context\StateContext;

/**
 * @method array getRoute()
 * @method void setRoute(array $route)
 *
 * @method array getClassDependencyMap()
 * @method void setClassDependencyMap(array $map)
 *
 * @method array getClassesToProcess()
 * @method void setClassesToProcess(array $classes)
 *
 * @method string getExtractedCode()
 * @method void setExtractedCode(string $code)
 *
 * @method array getTestMatrix()
 * @method void setTestMatrix(array $matrix)
 */
class RouteTestMatrixContext extends BaseContext
{
}
