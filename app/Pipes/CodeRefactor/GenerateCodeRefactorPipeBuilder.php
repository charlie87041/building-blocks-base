<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\CodeRefactor;

use BoostBrains\LaravelCodeCheck\Contexts\AppArchitectureContext;
use BoostBrains\LaravelCodeCheck\Contexts\RouteCodeRefactorContext;
use BoostBrains\LaravelCodeCheck\Contexts\RouteExecutionContext;
use BoostBrains\LaravelCodeCheck\Pipes\PipeBuilderInterface;
use BoostBrains\LaravelCodeCheck\WrapsTransactionsTrait;
use Illuminate\Pipeline\Pipeline;

class
GenerateCodeRefactorPipeBuilder implements PipeBuilderInterface
{
    use WrapsTransactionsTrait;

    protected RouteCodeRefactorContext $context;
    protected array $pipes;

    public function __construct(RouteCodeRefactorContext $context, array $pipes)
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
