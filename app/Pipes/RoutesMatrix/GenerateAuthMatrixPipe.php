<?php

namespace BoostBrains\LaravelCodeCheck\Pipes\RoutesMatrix;

class GenerateAuthMatrixPipe extends TestMatrix
{

    protected function getFlow()
    {
        return TestMatrix::AUTH_FLOW;
    }
}
