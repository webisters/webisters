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

use Framework\CLI\CLI;

/**
 * Class MakeController.
 *
 * @package webisters
 */
class MakeController extends NewCommand
{
    protected string $name = 'make:controller';
    protected string $description = 'Creates a controller in the app.';
    protected string $usage = 'make:controller <ControllerName|Nested/ControllerName>';

    public function run() : void
    {
        $controller = $this->console->getArgument(0);
        if ($controller === null || \trim($controller) === '') {
            CLI::error('Controller name is required');
            return;
        }

        $controller = \trim($controller);
        $controller = \str_replace('\\', '/', $controller);
        $controller = \trim($controller, '/');

        $segments = \array_values(\array_filter(\explode('/', $controller), static fn (string $segment) => $segment !== ''));
        if ($segments === []) {
            CLI::error('Controller name is required');
            return;
        }

        $className = (string) \array_pop($segments);
        $baseDirectory = $this->joinPath((string) \getcwd(), 'app', 'Controllers');
        $directory = $segments
            ? $this->joinPath($baseDirectory, ...$segments)
            : $baseDirectory;
        $directory = $this->normalizePath($directory);

        if (!\is_dir($directory) && !\mkdir($directory, 0755, true) && !\is_dir($directory)) {
            CLI::error('Unable to create controller directory');
            return;
        }

        $file = $this->joinPath($directory, $className . '.php');
        if (\is_file($file)) {
            CLI::error('Already exists');
            return;
        }

        $namespace = 'App\\Controllers';
        if ($segments) {
            $namespace .= '\\' . \implode('\\', $segments);
        }

        $contents = $this->getTemplate($namespace, $className);
        \file_put_contents($file, $contents);

        CLI::success('Controller created');
    }

    protected function getTemplate(string $namespace, string $className) : string
    {
        return <<<PHP
<?php declare(strict_types=1);

namespace {$namespace};

use Framework\MVC\Controller;

class {$className} extends Controller
{
    public function index() : string
    {
        return '{$className} works';
    }
}

PHP;
    }
}