<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix;

class GenerateValidationMatrixPipe extends TestMatrix
{

    protected function getFlow()
    {
        return TestMatrix::VALIDATION_FLOW;
    }
}
