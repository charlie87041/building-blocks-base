<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix;

class GenerateLogicMatrixPipe extends TestMatrix
{
    protected function getFlow()
    {
        return TestMatrix::LOGIC_FLOW;
    }
}
