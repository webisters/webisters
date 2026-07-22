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
 * Class MakeView.
 *
 * @package webisters
 */
class MakeView extends NewCommand
{
    protected string $name = 'make:view';
    protected string $description = 'Creates a view in the app.';
    protected string $usage = 'make:view <view/path>';

    public function run() : void
    {
        $view = $this->console->getArgument(0);
        if ($view === null || \trim($view) === '') {
            CLI::error('View name is required');
            return;
        }

        $view = \trim($view);
        $view = \str_replace('\\', '/', $view);
        $view = \trim($view, '/');

        if ($view === '') {
            CLI::error('View name is required');
            return;
        }

        $segments = \array_values(\array_filter(\explode('/', $view), static fn (string $segment) => $segment !== ''));
        $directorySegments = $segments;
        $fileName = (string) \array_pop($directorySegments);

        $baseDirectory = $this->joinPath((string) \getcwd(), 'app', 'Views');
        $directory = $directorySegments
            ? $this->joinPath($baseDirectory, ...$directorySegments)
            : $baseDirectory;
        $directory = $this->normalizePath($directory);

        if (!\is_dir($directory) && !\mkdir($directory, 0755, true) && !\is_dir($directory)) {
            CLI::error('Unable to create view directory');
            return;
        }

        $file = $this->joinPath($directory, $fileName . '.php');
        if (\is_file($file)) {
            CLI::error('Already exists');
            return;
        }

        $contents = $this->getTemplate($view);
        if (\file_put_contents($file, $contents) === false) {
            CLI::error('Unable to create view file');
            return;
        }

        CLI::success('View created');
        CLI::info($file);
    }

    protected function getTemplate(string $view) : string
    {
        return <<<PHP
            <h1>View: {$view}</h1>

            PHP;
    }
}
