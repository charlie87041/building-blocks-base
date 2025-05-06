<?php

namespace App\Console\Commands;

use App\Contexts\AppArchitectureContext;
use App\Pipes\PipeBuilder;
use Illuminate\Console\Command;

class RunCleanCodeNoRouteDependantCommand extends Command
{
    protected $signature = 'bb:app:is-cool';
    protected $description = 'Checks architectural comprobations using deptrac .';
    public function handle()
    {
        $context = new AppArchitectureContext([
        ]);

        $pipes = config('routeanalyzer.pipes.clean_code' );
        $pipeline = $this->buildPipeline($pipes, $context);
        $result = $pipeline->perform();

        if ($result instanceof \App\RuntimeErrorBag) {
            $this->error($result->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function buildPipeline(array $pipes, $context)
    {
        return PipeBuilder::makeProjectArchitectureCheckerBuilder($pipes, $context);
    }
}
