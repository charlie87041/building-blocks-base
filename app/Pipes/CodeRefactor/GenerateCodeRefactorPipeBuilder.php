<?php

namespace App\Pipes\CodeRefactor;

use App\Contexts\AppArchitectureContext;
use App\Contexts\RouteCodeRefactorContext;
use App\Contexts\RouteExecutionContext;
use App\Pipes\PipeBuilderInterface;
use App\WrapsTransactionsTrait;
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
