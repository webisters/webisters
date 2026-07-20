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
use Framework\CLI\Command;
use Framework\CLI\Styles\ForegroundColor;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class NewCommand.
 *
 * @package webisters
 */
abstract class NewCommand extends Command
{
    protected ?string $projectNameFromPrompt = null;
    protected bool $headerShown = false;

    /**
     * PHP extensions that must be enabled before creating a project. The
     * runtime extensions are required by the framework libraries; `openssl`
     * and `zip` are required by Composer itself to fetch and extract packages
     * during `composer create-project`.
     *
     * @var array<int, string>
     */
    protected array $requiredExtensions = [
        'intl', 'sodium', 'gd', 'mysqli', 'curl', 'fileinfo',
        'json', 'simplexml', 'dom', 'libxml', 'openssl', 'zip',
    ];

    protected function showHeaderOnce() : void
    {
        if ($this->headerShown) {
            return;
        }
        $this->headerShown = true;
        $this->showHeader();
        CLI::newLine();
    }

    protected function showHeader() : void
    {
        $text = <<<'EOL'
        $$\      $$\ $$$$$$$$\ $$$$$$$\  $$$$$$\  $$$$$$\ $$$$$$$$\ $$$$$$$$\ $$$$$$$\   $$$$$$\  
        $$ | $\  $$ |$$  _____|$$  __$$\ \_$$  _|$$  __$$\\__$$  __|$$  _____|$$  __$$\ $$  __$$\ 
        $$ |$$$\ $$ |$$ |      $$ |  $$ |  $$ |  $$ /  \__|  $$ |   $$ |      $$ |  $$ |$$ /  \__|
        $$ $$ $$\$$ |$$$$$\    $$$$$$$\ |  $$ |  \$$$$$$\    $$ |   $$$$$\    $$$$$$$  |\$$$$$$\  
        $$$$  _$$$$ |$$  __|   $$  __$$\   $$ |   \____$$\   $$ |   $$  __|   $$  __$$<  \____$$\ 
        $$$  / \$$$ |$$ |      $$ |  $$ |  $$ |  $$\   $$ |  $$ |   $$ |      $$ |  $$ |$$\   $$ |
        $$  /   \$$ |$$$$$$$$\ $$$$$$$  |$$$$$$\ \$$$$$$  |  $$ |   $$$$$$$$\ $$ |  $$ |\$$$$$$  |
        \__/     \__|\________|\_______/ \______| \______/   \__|   \________|\__|  \__| \______/ 
        EOL;
        CLI::write($text, ForegroundColor::green);
    }

    /**
     * Verify all required PHP extensions are enabled before attempting an
     * install. On failure it prints the missing extensions plus how to enable
     * them, and returns false so the caller can abort cleanly.
     */
    protected function ensureRequiredExtensions() : bool
    {
        $missing = [];
        foreach ($this->requiredExtensions as $extension) {
            if (!\extension_loaded($extension)) {
                $missing[] = $extension;
            }
        }

        if ($missing === []) {
            return true;
        }

        CLI::error('Missing required PHP extensions: ' . \implode(', ', $missing), null);
        CLI::newLine();
        CLI::write('Enable the extensions above, then run the command again.', ForegroundColor::yellow);
        CLI::write(
            '  Windows: uncomment the matching "extension=..." lines in your php.ini'
            . ' (locate it with "php --ini"), then restart your terminal.',
            ForegroundColor::yellow
        );
        CLI::write(
            '  Ubuntu/Debian: sudo apt install '
            . \implode(' ', \array_map(static fn (string $e) : string => 'php-' . $e, $missing)),
            ForegroundColor::yellow
        );
        CLI::write('  Verify with: php -m', ForegroundColor::yellow);
        CLI::newLine();
        CLI::write(
            'Details: https://docs.webisters.com/guides/webisters/#requirements',
            ForegroundColor::yellow
        );

        return false;
    }

    protected function monorepoRoot() : string
    {
        return $this->normalizePath($this->joinPath(__DIR__, '..', '..', '..', '..'));
    }

    protected function joinPath(string ...$segments) : string
    {
        $clean = [];
        foreach ($segments as $index => $segment) {
            if ($segment === '') {
                continue;
            }
            if ($index === 0) {
                $clean[] = \rtrim($segment, '/\\');
                continue;
            }
            $clean[] = \trim($segment, '/\\');
        }
        return \implode(\DIRECTORY_SEPARATOR, $clean);
    }

    protected function normalizePath(string $path) : string
    {
        $realpath = \realpath($path);
        if ($realpath !== false) {
            return \rtrim($realpath, '/\\');
        }
        return \rtrim($path, '/\\');
    }

    protected function isAbsolutePath(string $path) : bool
    {
        return (bool) \preg_match('/^(?:[A-Za-z]:[\\\\\\/]|\\\\\\\\|\\/)/', $path);
    }

    protected function create(string $package, string $name) : void
    {
        if (!$this->ensureRequiredExtensions()) {
            return;
        }

        $this->showHeaderOnce();

        $directory = $this->getDirectoryPath();

        $source = $this->getTemplateSource($package);
        if ($source) {
            $this->ensureDirectoryExists($directory);
            $this->copyDir($source, $directory);
        } else {
            if (!$this->createProjectWithComposer('webisters/' . $package, $directory)) {
                return;
            }
        }

        $this->ensureProjectCliFile($directory);

        $projectName = $this->resolveProjectName($directory);
        $projectDescription = $this->resolveProjectDescription($projectName, $name);
        $this->updateComposerMetadata($directory, $projectName, $projectDescription);

        CLI::write(
            $name . ' structure created at "' . $directory . '"',
            ForegroundColor::green
        );

        if ($this->shouldRunComposerInstall()) {
            $this->runComposerInstall($directory);
        }
    }

    protected function createProjectWithComposer(string $package, string $directory) : bool
    {
        CLI::info('Downloading template via composer create-project...');

        $parent = $this->normalizePath((string) \dirname($directory));
        $target = (string) \basename($directory);
        if ($target === '' || $target === '.' || $target === '..') {
            CLI::error('Invalid target directory name.', null);
            return false;
        }

        if (!\is_dir($parent)) {
            if (!@\mkdir($parent, 0755, true) && !\is_dir($parent)) {
                CLI::error('Unable to create parent directory: ' . $parent, null);
                return false;
            }
        }

        $cmd = 'composer create-project --no-interaction --no-install '
            . \escapeshellarg($package)
            . ' '
            . \escapeshellarg($target);

        $currentDirectory = (string) \getcwd();
        if (!\chdir($parent)) {
            CLI::error('Unable to change directory for composer create-project.', null);
            return false;
        }

        try {
            $exitCode = 1;
            \passthru($cmd, $exitCode);
        } finally {
            \chdir($currentDirectory);
        }

        if ($exitCode !== 0 || !\is_dir($directory)) {
            CLI::error('Failed to download template. Make sure composer is installed and the package exists on Packagist.', null);
            return false;
        }

        return true;
    }

    protected function ensureDirectoryExists(string $directory) : void
    {
        if (\file_exists($directory)) {
            CLI::error(\sprintf('The path "%s" already exists', $directory));
        }

        if (!\mkdir($directory, 0755, true) && !\is_dir($directory)) {
            CLI::error(\sprintf('Directory "%s" could not be created', $directory));
        }

        $realpath = \realpath($directory);
        if ($realpath === false) {
            CLI::error(\sprintf('Was not possible get the realpath of "%s"', $directory));
        }
    }

    protected function shouldRunComposerInstall() : bool
    {
        if (!$this->isInteractiveInput()) {
            return false;
        }

        while (true) {
            $answer = \strtolower(\trim(CLI::prompt(
                'Run composer install in this project now?',
                ['yes', 'no']
            )));

            if ($answer === 'yes' || $answer === 'y') {
                return true;
            }

            if ($answer === 'no' || $answer === 'n') {
                return false;
            }

            CLI::error('Please answer yes or no.', null);
        }
    }

    protected function runComposerInstall(string $directory) : void
    {
        CLI::info('Running composer install...');

        $currentDirectory = (string) \getcwd();
        $exitCode = 1;

        if (!\chdir($directory)) {
            CLI::error('Unable to change directory to the new project.', null);
            return;
        }

        try {
            $exitCode = $this->executeComposerInstall('composer install');

            if ($exitCode !== 0) {
                CLI::info('Composer install failed. Retrying once after cleanup...');
                $this->cleanupComposerMetadata();
                $exitCode = $this->executeComposerInstall('composer install --no-cache');
            }
        } finally {
            \chdir($currentDirectory);
        }

        if ($exitCode !== 0) {
            CLI::error('Composer install failed. Run it manually in the project directory.', null);
            return;
        }

        CLI::success('Composer install completed.');
    }

    protected function resolveProjectName(string $directory) : string
    {
        if ($this->projectNameFromPrompt !== null) {
            return $this->normalizePackageSegment($this->projectNameFromPrompt);
        }

        $default = \basename($directory);
        if ($this->isInteractiveInput()) {
            $answer = \trim(CLI::prompt('Project name', $default));
            if ($answer !== '') {
                $default = $answer;
            }
        }

        return $this->normalizePackageSegment($default);
    }

    protected function resolveProjectDescription(string $projectName, string $fallbackName) : string
    {
        $default = $this->toHumanText($projectName) . ' ' . $fallbackName;
        if (!$this->isInteractiveInput()) {
            return $default;
        }

        $answer = \trim(CLI::prompt('Project description', $default));
        if ($answer === '') {
            return $default;
        }

        return $answer;
    }

    protected function updateComposerMetadata(
        string $directory,
        string $projectName,
        string $projectDescription
    ) : void {
        $composerFile = $this->joinPath($directory, 'composer.json');
        if (!\is_file($composerFile)) {
            return;
        }

        $contents = \file_get_contents($composerFile);
        if ($contents === false) {
            return;
        }

        $vendor = 'webisters';
        if (\preg_match('/"name"\s*:\s*"([^"\/]+)\/[^\"]*"/', $contents, $matches) === 1) {
            $vendor = $matches[1];
        }

        $composerName = $vendor . '/' . $projectName;
        $composerName = $this->escapeJsonString($composerName);
        $projectDescription = $this->escapeJsonString($projectDescription);

        $updated = \preg_replace(
            '/"name"\s*:\s*"[^"]*"/',
            '"name": "' . $composerName . '"',
            $contents,
            1
        );
        if (!\is_string($updated)) {
            return;
        }

        $updated = \preg_replace(
            '/"description"\s*:\s*"[^"]*"/',
            '"description": "' . $projectDescription . '"',
            $updated,
            1
        );
        if (!\is_string($updated)) {
            return;
        }

        \file_put_contents($composerFile, $updated);
    }

    protected function normalizePackageSegment(string $value) : string
    {
        $value = \strtolower(\trim($value));
        $value = (string) \preg_replace('/[^a-z0-9._-]+/', '-', $value);
        $value = \trim($value, '-._');

        return $value !== '' ? $value : 'project';
    }

    protected function toHumanText(string $value) : string
    {
        $value = \str_replace(['-', '_', '.'], ' ', $value);
        $value = (string) \preg_replace('/\s+/', ' ', $value);
        return \ucwords(\trim($value));
    }

    protected function escapeJsonString(string $value) : string
    {
        return \str_replace(
            ['\\', '"'],
            ['\\\\', '\\"'],
            $value
        );
    }

    protected function executeComposerInstall(string $command) : int
    {
        $exitCode = 1;
        \passthru($command, $exitCode);
        return $exitCode;
    }

    protected function cleanupComposerMetadata() : void
    {
        $composerDir = 'vendor' . \DIRECTORY_SEPARATOR . 'composer';
        foreach (['installed.php', 'installed.json'] as $file) {
            $path = $composerDir . \DIRECTORY_SEPARATOR . $file;
            if (!\is_file($path)) {
                continue;
            }

            if (!@\unlink($path)) {
                CLI::info('Could not remove ' . $path . '. Continuing...');
            }
        }

        $binDir = 'vendor' . \DIRECTORY_SEPARATOR . 'bin';
        foreach (['phpunit', 'phpunit.bat', 'php-cs-fixer', 'php-cs-fixer.bat'] as $file) {
            $path = $binDir . \DIRECTORY_SEPARATOR . $file;
            if (!\is_file($path)) {
                continue;
            }

            if (!@\unlink($path)) {
                CLI::info('Could not remove ' . $path . '. Continuing...');
            }
        }
    }

    protected function isInteractiveInput() : bool
    {
        if (!\defined('STDIN') || !\is_resource(\STDIN)) {
            return false;
        }

        if (!\function_exists('stream_isatty')) {
            return true;
        }

        return \stream_isatty(\STDIN);
    }

    protected function getTemplateSource(string $package) : false | string
    {
        $sources = [
            // Monorepo package templates.
            $this->joinPath($this->monorepoRoot(), 'packages', $package),
            // Monorepo project templates fallback.
            $this->joinPath($this->monorepoRoot(), 'projects', $package),
            // Installed sibling packages in vendor/webisters/<package>.
            $this->joinPath(__DIR__, '..', '..', '..', $package),
            // Local composer install.
            $this->joinPath(__DIR__, '..', '..', 'vendor', 'webisters', $package),
            // Global composer install.
            $this->joinPath(__DIR__, '..', '..', '..', '..', '..', 'vendor', 'webisters', $package),
        ];

        foreach ($sources as $source) {
            if (\is_dir($source)) {
                return $this->normalizePath($source);
            }
        }

        return false;
    }

    protected function getComposerSource(string $package, bool $global = false) : false | string
    {
        $source = $global
            ? $this->joinPath(__DIR__, '..', '..', '..', '..', '..')
            : $this->joinPath(__DIR__, '..', '..');
        $source = $this->joinPath($source, 'vendor', 'webisters', $package);
        if (\is_dir($source)) {
            return $this->normalizePath($source);
        }
        return false;
    }

    protected function getDistroSource(string $package) : false | string
    {
        $source = $this->joinPath(__DIR__, '..', '..', '..', '..', 'packages', $package);
        if (\is_dir($source)) {
            return $this->normalizePath($source);
        }
        return false;
    }

    protected function copyDir(string $source, string $directory) : void
    {
        $skipRoots = [
            '.git',
            '.idea',
            '.vscode',
            'vendor',
            'node_modules',
        ];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $subPath = $iterator->getSubPathname();
            $rootSegment = \explode(\DIRECTORY_SEPARATOR, $subPath)[0] ?? '';
            if (\in_array($rootSegment, $skipRoots, true)) {
                continue;
            }

            if ($item->isDir()) {
                $dir = $directory . \DIRECTORY_SEPARATOR . $subPath;
                if ( ! \mkdir($dir, 0755, true) && ! \is_dir($dir)) {
                    CLI::error(
                        \sprintf('Directory "%s" could not be created', $dir)
                    );
                }
                continue;
            }
            \copy((string) $item, $directory . \DIRECTORY_SEPARATOR . $subPath);
        }
    }

    protected function ensureProjectCliFile(string $directory) : void
    {
        $file = $this->joinPath($directory, 'webisters');
        $content = <<<'PHP'
#!/usr/bin/env php
<?php

declare(strict_types=1);

$argv = $_SERVER['argv'] ?? [];
$command = $argv[1] ?? null;

if ($command === 'start') {
    $portArg = $argv[2] ?? '8000';
    if (!ctype_digit($portArg)) {
        fwrite(STDERR, "Invalid port. Use an integer between 1 and 65535." . PHP_EOL);
        exit(1);
    }
    $port = (int) $portArg;
    if ($port < 1 || $port > 65535) {
        fwrite(STDERR, "Invalid port. Use an integer between 1 and 65535." . PHP_EOL);
        exit(1);
    }

    $documentRoot = is_dir(__DIR__ . '/public')
        ? realpath(__DIR__ . '/public')
        : realpath(__DIR__);
    if (!is_string($documentRoot) || $documentRoot === '') {
        fwrite(STDERR, "Cannot resolve document root." . PHP_EOL);
        exit(1);
    }

    $routerScript = is_file($documentRoot . '/index.php')
        ? $documentRoot . '/index.php'
        : null;

    echo PHP_EOL . "Starting Server" . PHP_EOL . PHP_EOL;
    echo 'http://127.0.0.1:' . $port . PHP_EOL . PHP_EOL;

    $cmd = escapeshellarg(PHP_BINARY)
        . ' -S 127.0.0.1:' . $port
        . ' -t ' . escapeshellarg($documentRoot);
    if ($routerScript !== null) {
        $cmd .= ' ' . escapeshellarg($routerScript);
    }
    passthru($cmd, $exitCode);
    exit((int) $exitCode);
}

if (is_file(__DIR__ . '/boot/app.php')) {
    require __DIR__ . '/vendor/autoload.php';
    $app = require __DIR__ . '/boot/app.php';
    $app->runCli();
    exit(0);
}

if (is_file(__DIR__ . '/vendor/bin/webisters')) {
    require __DIR__ . '/vendor/bin/webisters';
    exit(0);
}

fwrite(STDERR, "Unable to find CLI bootstrap for this project." . PHP_EOL);
exit(1);
PHP;

        \file_put_contents($file, $content . \PHP_EOL);
        if (\DIRECTORY_SEPARATOR === '/') {
            @\chmod($file, 0755);
        }
    }

    protected function getDirectoryPath() : string
    {
        $directory = $this->console->getArgument(0);
        if ($directory === null) {
            $directory = $this->promptDirectory();
        }
        if (!$this->isAbsolutePath($directory)) {
            $directory = $this->joinPath((string) \getcwd(), $directory);
        }
        $directory = $this->normalizePath($directory);
        if (\file_exists($directory)) {
            CLI::error(\sprintf('The path "%s" already exists', $directory));
        }
        return $directory;
    }

    protected function promptDirectory() : string
    {
        $name = \trim(CLI::prompt('Project name'));
        if ($name === '') {
            CLI::error('Project name cannot be empty. Try again.', null);
            return $this->promptDirectory();
        }

        $this->projectNameFromPrompt = $name;
        return $name;
    }
}
