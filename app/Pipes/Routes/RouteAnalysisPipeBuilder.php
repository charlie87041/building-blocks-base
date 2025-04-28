<?php

namespace App\Pipes\Routes;

use App\Contexts\RouteAnalysisContext;
use App\Pipes\PipeBuilderInterface;
use App\WrapsTransactionsTrait;
use Illuminate\Pipeline\Pipeline;

class RouteAnalysisPipeBuilder implements PipeBuilderInterface
{
    use WrapsTransactionsTrait;

    protected RouteAnalysisContext $context;
    protected array $pipes;

    public function __construct(RouteAnalysisContext $context, array $pipes)
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
