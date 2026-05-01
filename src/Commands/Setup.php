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

/**
 * Adds Composer global bin directory to PATH.
 */
final class Setup extends Command
{
    protected string $name = 'setup';
    protected string $description = 'Configures your environment for the webisters command.';
    protected string $usage = 'setup';

    public function run() : void
    {
        $binDir = $this->resolveComposerGlobalBinDir();
        if ($binDir === null) {
            CLI::error('Unable to determine Composer global bin directory.', null);
            $this->printManualInstructions();
            return;
        }

        if (\PHP_OS_FAMILY !== 'Windows') {
            CLI::info('Automatic PATH setup is currently supported on Windows only.');
            CLI::write('Composer global bin-dir: ' . $binDir);
            $this->printManualInstructions($binDir);
            return;
        }

        $this->setupWindowsPath($binDir);
    }

    private function resolveComposerGlobalBinDir() : ?string
    {
        $output = @\shell_exec('composer global config bin-dir --absolute 2>NUL');
        if (\is_string($output)) {
            $binDir = \trim($output);
            if ($binDir !== '') {
                return $binDir;
            }
        }

        $appData = \getenv('APPDATA');
        if (\is_string($appData) && $appData !== '') {
            $fallback = \rtrim($appData, '/\\') . \DIRECTORY_SEPARATOR . 'Composer' . \DIRECTORY_SEPARATOR
                . 'vendor' . \DIRECTORY_SEPARATOR . 'bin';
            return $fallback;
        }

        return null;
    }

    private function setupWindowsPath(string $binDir) : void
    {
        $binDir = \rtrim($binDir, "\\/");

        $ps = <<<'PS'
$ErrorActionPreference = 'Stop'
$bin = $args[0]
$userPath = [Environment]::GetEnvironmentVariable('Path','User')
if (-not $userPath) { $userPath = '' }
$parts = $userPath -split ';' | Where-Object { $_ -ne '' }
$already = $false
foreach ($p in $parts) {
    if ($p.TrimEnd('\\') -ieq $bin) { $already = $true; break }
}
if ($already) {
    Write-Output 'already'
    exit 0
}
$newPath = if ($userPath -and -not $userPath.TrimEnd().EndsWith(';')) { $userPath + ';' + $bin } else { $userPath + $bin }
[Environment]::SetEnvironmentVariable('Path', $newPath, 'User')
Write-Output 'added'
PS;

        $command = 'powershell -NoProfile -ExecutionPolicy Bypass -Command ' . \escapeshellarg($ps)
            . ' -- ' . \escapeshellarg($binDir);

        $result = @\shell_exec($command);
        $result = \is_string($result) ? \trim($result) : '';

        if ($result === 'already') {
            CLI::success('PATH is already configured.');
            CLI::write('You can now run: webisters new-app <name>');
            return;
        }

        if ($result === 'added') {
            CLI::success('Added Composer global bin directory to your user PATH.');
            CLI::write('Bin dir: ' . $binDir);
            CLI::info('Restart your terminal for changes to take effect.');
            CLI::write('Then you can run: webisters new-app <name>');
            return;
        }

        CLI::error('Automatic PATH update failed.', null);
        CLI::write('Composer global bin-dir: ' . $binDir);
        $this->printManualInstructions($binDir);
    }

    private function printManualInstructions(?string $binDir = null) : void
    {
        if ($binDir === null) {
            $binDir = '<composer-global-bin-dir>';
        }

        if (\PHP_OS_FAMILY === 'Windows') {
            CLI::write('Manual fallback (Windows):');
            CLI::write('  Add this to your user PATH: ' . $binDir);
            CLI::write('  Or run commands without PATH using: composer global exec webisters <command>');
            return;
        }

        CLI::write('Manual fallback (Linux/macOS):');
        CLI::write('  export PATH="' . $binDir . ':$PATH"');
        CLI::write('  Or run commands without PATH using: composer global exec webisters <command>');
    }
}
