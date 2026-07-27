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
class Setup extends Command
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

        if (!$this->isWindows()) {
            CLI::info('Automatic PATH setup is currently supported on Windows only.');
            CLI::write('Composer global bin-dir: ' . $binDir);
            $this->printManualInstructions($binDir);
            return;
        }

        $this->setupWindowsPath($binDir);
    }

    protected function resolveComposerGlobalBinDir() : ?string
    {
        $output = $this->executeComposerBinDir();
        if (\is_string($output)) {
            $binDir = \trim($output);
            if ($binDir !== '') {
                return $binDir;
            }
        }

        $appData = \getenv('APPDATA');
        if (\is_string($appData) && $appData !== '') {
            return \rtrim($appData, '/\\') . \DIRECTORY_SEPARATOR . 'Composer' . \DIRECTORY_SEPARATOR
                . 'vendor' . \DIRECTORY_SEPARATOR . 'bin';
        }

        return null;
    }

    protected function setupWindowsPath(string $binDir) : void
    {
        $binDir = \rtrim($binDir, '\/');

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

        $result = $this->executePowerShellSetPath($command);
        $result = \is_string($result) ? \trim($result) : '';

        if ($result === 'already') {
            CLI::success('PATH is already configured.');
            CLI::write('Bin dir: ' . $binDir);
            CLI::newLine();
            CLI::info('If webisters is still not found, open a NEW terminal.');
            CLI::write('Command Prompt, PowerShell, and Git Bash all read the updated user PATH,');
            CLI::write('but a session opened before setup ran will not see it.');
            CLI::newLine();
            CLI::write('Verify with: webisters --version  (or: where webisters)');
            CLI::write('You can now run: webisters new-app <name>');
            return;
        }

        if ($result === 'added') {
            CLI::success('Added Composer global bin directory to your user PATH.');
            CLI::write('Bin dir: ' . $binDir);
            CLI::newLine();
            CLI::info('Open a NEW terminal for the change to take effect.');
            CLI::write('Command Prompt, PowerShell, and Git Bash all read the updated user PATH,');
            CLI::write('but sessions that were already open will not see it until you reopen them.');
            CLI::newLine();
            CLI::write('Verify in the new terminal with:');
            CLI::write('  webisters --version');
            CLI::write('  where webisters');
            CLI::write('Then you can run: webisters new-app <name>');
            return;
        }

        CLI::error('Automatic PATH update failed.', null);
        CLI::write('Composer global bin-dir: ' . $binDir);
        $this->printManualInstructions($binDir);
    }

    protected function printManualInstructions(?string $binDir = null) : void
    {
        if ($binDir === null) {
            $binDir = '<composer-global-bin-dir>';
        }

        if ($this->isWindows()) {
            CLI::write('Manual fallback (Windows):');
            CLI::write('  Add this to your user PATH: ' . $binDir);
            CLI::write('  Or run commands without PATH using: composer global exec webisters <command>');
            return;
        }

        CLI::write('Manual fallback (Linux/macOS):');
        CLI::write('  export PATH="' . $binDir . ':$PATH"');
        CLI::write('  Or run commands without PATH using: composer global exec webisters <command>');
    }

    protected function isWindows() : bool
    {
        return \PHP_OS_FAMILY === 'Windows';
    }

    protected function executeComposerBinDir() : ?string
    {
        return @\shell_exec('composer global config bin-dir --absolute 2>NUL');
    }

    protected function executePowerShellSetPath(string $command) : ?string
    {
        return @\shell_exec($command);
    }
}
