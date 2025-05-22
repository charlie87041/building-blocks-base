<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix;

use BoostBrains\LaravelCodeCheck\Contexts\RouteAnalysisContext;
use BoostBrains\LaravelCodeCheck\Contexts\RouteExecutionContext;
use BoostBrains\LaravelCodeCheck\Contexts\RouteTestMatrixContext;
use BoostBrains\LaravelCodeCheck\Pipes\PipeBuilderInterface;
use BoostBrains\LaravelCodeCheck\WrapsTransactionsTrait;
use Illuminate\Pipeline\Pipeline;

class TestMatrixPipeBuilder implements PipeBuilderInterface
{
    use WrapsTransactionsTrait;

    protected RouteTestMatrixContext $context;
    protected array $pipes;

    public function __construct(RouteTestMatrixContext $context, array $pipes)
    {
        $this->context = $context;
        $this->pipes = $pipes;
    }

    public function perform()
    {
        return $this->executeAndReturnBagIfAny(function () {
            return app(Pipeline::class)
                ->send($this->context)
                ->through($this->pipes)
                ->thenReturn();
        });
    }
}
