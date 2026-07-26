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
 * Class Check.
 *
 * @package webisters
 */
class Check extends Command
{
    protected string $name = 'check';
    protected string $description = 'Reports PHP, extension, and Composer status.';
    protected string $usage = 'check';
    protected string $group = 'Diagnostics';

    public function run() : void
    {
        CLI::success('Webisters environment check');
        CLI::newLine();

        CLI::write('PHP version: ' . $this->getPhpVersion());
        CLI::write('Extensions:');
        foreach ($this->getExtensionsToCheck() as $extension) {
            CLI::write('  ' . $extension . ': ' . ($this->isExtensionLoaded($extension) ? 'enabled' : 'missing'));
        }
        CLI::newLine();
        CLI::write('Composer: ' . $this->getComposerStatus());
    }

    protected function getPhpVersion() : string
    {
        return \PHP_VERSION;
    }

    /**
     * @return array<int, string>
     */
    protected function getExtensionsToCheck() : array
    {
        return [
            'intl',
            'sodium',
            'gd',
            'mysqli',
            'curl',
            'fileinfo',
            'json',
            'simplexml',
            'dom',
            'libxml',
            'openssl',
            'zip',
        ];
    }

    protected function isExtensionLoaded(string $extension) : bool
    {
        return \extension_loaded($extension);
    }

    protected function getComposerStatus() : string
    {
        $output = [];
        $exitCode = $this->executeComposerVersion($output);

        if ($exitCode !== 0 || $output === []) {
            return 'unavailable';
        }

        return \trim(\implode(\PHP_EOL, $output));
    }

    /**
     * @param array<int, string> $output
     */
    protected function executeComposerVersion(array &$output) : int
    {
        $exitCode = 1;
        \exec('composer --version 2>&1', $output, $exitCode);
        return $exitCode;
    }
}
