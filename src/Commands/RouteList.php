<?php declare(strict_types=1);
/*
 * This file is part of Webisters Command Line Tool.
 *
 * (c) Hafiz Muhammad Moaz <thewebisters@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Webisters\Commands;

use Closure;
use Framework\CLI\CLI;
use Framework\CLI\Command;
use Framework\MVC\App;

/**
 * Class RouteList.
 *
 * @package webisters
 */
class RouteList extends Command
{
    protected string $name = 'route:list';
    protected string $description = 'Displays all registered routes.';
    protected string $usage = 'route:list';
    protected string $group = 'Routing';

    public function run() : void
    {
        $this->bootApp();
        $routes = $this->collectRoutes();

        if ($routes === []) {
            CLI::info('No routes found.');
            return;
        }

        CLI::success('Registered routes');
        CLI::newLine();
        CLI::table($routes, ['METHOD', 'URI', 'ACTION']);
    }

    protected function bootApp() : void
    {
        $bootstrap = $this->getBootstrapFile();
        if (!\is_file($bootstrap)) {
            CLI::error('App bootstrap not found');
            return;
        }
        require_once $bootstrap;
    }

    protected function getBootstrapFile() : string
    {
        return \getcwd() . \DIRECTORY_SEPARATOR . 'boot' . \DIRECTORY_SEPARATOR . 'app.php';
    }

    /**
     * @return array<int,array<string,string>>
     */
    protected function collectRoutes() : array
    {
        $rows = [];
        foreach (App::router()->getCollections() as $collection) {
            foreach ($collection->routes as $method => $routes) {
                foreach ($routes as $route) {
                    $rows[] = [
                        'METHOD' => $method,
                        'URI' => $route->getPath(),
                        'ACTION' => $this->formatAction($route->getAction()),
                    ];
                }
            }
        }
        \usort($rows, static function (array $first, array $second) : int {
            $result = \strcmp($first['METHOD'], $second['METHOD']);
            if ($result !== 0) {
                return $result;
            }
            $result = \strcmp($first['URI'], $second['URI']);
            if ($result !== 0) {
                return $result;
            }
            return \strcmp($first['ACTION'], $second['ACTION']);
        });
        return $rows;
    }

    protected function formatAction(Closure | string $action) : string
    {
        if ($action instanceof Closure) {
            return 'Closure';
        }
        $action = \trim($action, '\\');
        $prefix = 'App\Controllers\\';
        if (\str_starts_with($action, $prefix)) {
            $action = \substr($action, \strlen($prefix));
        }
        return $action;
    }
}
