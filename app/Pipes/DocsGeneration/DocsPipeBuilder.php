<?php

namespace App\Pipes\DocsGeneration;

use App\Contexts\RouteAnalysisContext;
use App\Contexts\RouteExecutionContext;
use App\Contexts\RouteTestMatrixContext;
use App\Pipes\PipeBuilderInterface;
use App\WrapsTransactionsTrait;
use Illuminate\Pipeline\Pipeline;

class DocsPipeBuilder implements PipeBuilderInterface
{
    use WrapsTransactionsTrait;

    protected RouteExecutionContext $context;
    protected array $pipes;

    public function __construct(RouteExecutionContext $context, array $pipes)
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
