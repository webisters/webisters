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
 * Class MakeModel.
 *
 * @package webisters
 */
class MakeModel extends NewCommand
{
    protected string $name = 'make:model';
    protected string $description = 'Creates a model in the app.';
    protected string $usage = 'make:model <ModelName|Nested/ModelName>';

    public function run() : void
    {
        $model = $this->console->getArgument(0);
        if ($model === null || \trim($model) === '') {
            CLI::error('Model name is required');
            return;
        }

        $model = \trim($model);
        $model = \str_replace('\\', '/', $model);
        $model = \trim($model, '/');

        $segments = \array_values(\array_filter(\explode('/', $model), static fn (string $segment) => $segment !== ''));
        if ($segments === []) {
            CLI::error('Model name is required');
            return;
        }

        $className = (string) \array_pop($segments);
        $baseDirectory = $this->joinPath((string) \getcwd(), 'app', 'Models');
        $directory = $segments
            ? $this->joinPath($baseDirectory, ...$segments)
            : $baseDirectory;
        $directory = $this->normalizePath($directory);

        if (!\is_dir($directory) && !\mkdir($directory, 0755, true) && !\is_dir($directory)) {
            CLI::error('Unable to create model directory');
            return;
        }

        $file = $this->joinPath($directory, $className . '.php');
        if (\is_file($file)) {
            CLI::error('Already exists');
            return;
        }

        $namespace = 'App\\Models';
        if ($segments) {
            $namespace .= '\\' . \implode('\\', $segments);
        }

        $contents = $this->getTemplate($namespace, $className);
        if (\file_put_contents($file, $contents) === false) {
            CLI::error('Unable to create model file');
            return;
        }

        CLI::success('Model created');
        CLI::info($file);
    }

    protected function getTemplate(string $namespace, string $className) : string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Framework\MVC\Model;

class {$className} extends Model
{
}

PHP;
    }
}