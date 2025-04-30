<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Pipes\PipeBuilder;
use App\Support\Storage\GraphStorageFactory;

abstract class BaseRouteCommand extends Command
{
    public function handle()
    {
        $this->info('Starting: ' . $this->getDescriptionLabel());


        $routes = app('router')->getRoutes();

        foreach ($routes as $route) {
            /** @var \Illuminate\Routing\Route $route */
            $controllerAction = $route->getAction('controller');

            if (!$controllerAction) {
                continue;
            }

            [$controller, $method] = $this->resolveControllerAndMethod($controllerAction);

            $context = $this->buildContext($route, $controller, $method);

            $pipes = config('routeanalyzer.pipes.' . $this->getPipelineConfigKey());
            $pipeline = $this->buildPipeline($pipes, $context);
            $result = $pipeline->perform();

            if ($result instanceof \App\RuntimeErrorBag) {
                $this->error('Error analyzing route: ' . $route->uri);
                $this->error($result->getMessage());
                continue;
            }
            $this->info('Finished analyzing route: ' . $route->uri);
        }

        $this->info($this->getDescriptionLabel() . ' completed successfully.');
        return Command::SUCCESS;
    }

    protected function resolveControllerAndMethod($controllerAction): array
    {
        if (str_contains($controllerAction, '@')) {
            return explode('@', $controllerAction);
        }
        return [$controllerAction, '__invoke'];
    }

    protected function loadClassDependencyMap($storage)
    {
        return $storage->load('route-analysis/class-dependency-map.json');
    }

    protected abstract function buildPipeline(array $pipes, $context);

    abstract protected function buildContext($route, $controller, $method);

    abstract protected function getPipelineConfigKey(): string;

    abstract protected function getDescriptionLabel(): string;
}
