<?php

namespace App\Console\Commands;

use App\Contexts\RouteAnalysisContext;
use App\Pipes\PipeBuilder;
use Illuminate\Console\Command;

class AnalyzeRoutesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bb:make';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Route Analysis...');

        $routes = $this->getRoutes();

        if (empty($routes)) {
            $this->warn('No routes found.');
            return Command::FAILURE;
        }

        foreach ($routes as $route) {
            $this->info('Analyzing route: ' . $route['uri']);

            $context = new RouteAnalysisContext([
                'route' => $route,
            ]);

            $pipes = config('routeanalyzer.pipes.routes');

            $pipeline = PipeBuilder::makeRouteBuilder($pipes, $context);

            $result = $pipeline->perform();

            if ($result instanceof \App\RuntimeErrorBag) {
                $this->error('Error analyzing route: ' . $route['uri']);
                $this->error($result->getMessage());
                continue;
            }

            $this->info('Finished analyzing route: ' . $route['uri']);
        }

        $this->info('Route Analysis completed successfully.');
        return Command::SUCCESS;
    }

    protected function getRoutes(): array
    {
        $routes = [];

        foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
            $action = $route->getActionName();

            if ($action === 'Closure') {
                continue; // TODO - Add support for closure routes
            }

            if (str_contains($action, '@')) {
                [$controller, $method] = explode('@', $action);
            } else {
                $controller = $action;
                $method = '__invoke'; // Asumimos invokable controller
            }

            $routes[] = [
                'uri' => $route->uri(),
                'controller' => $controller,
                'method' => $method,
            ];
        }

        return $routes;
    }
}
