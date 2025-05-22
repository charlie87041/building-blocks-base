<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\CleanCode;

use BoostBrains\LaravelCodeCheck\Contexts\AppArchitectureContext;
use BoostBrains\LaravelCodeCheck\Pipes\PipeBuilderInterface;
use BoostBrains\LaravelCodeCheck\WrapsTransactionsTrait;
use Illuminate\Pipeline\Pipeline;

class
CleanCodePipeBuilder implements PipeBuilderInterface
{
    use WrapsTransactionsTrait;

    protected AppArchitectureContext $context;
    protected array $pipes;

    public function __construct(AppArchitectureContext $context, array $pipes)
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
