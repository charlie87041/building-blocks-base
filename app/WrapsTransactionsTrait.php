<?php

namespace BoostBrains\LaravelCodeCheck;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait WrapsTransactionsTrait
{
    public function executeAndReturnBagIfAny(callable $callback)
    {
        try {
            return DB::transaction($callback);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
            return new RuntimeErrorBag($exception->getMessage());
        }
    }
}
