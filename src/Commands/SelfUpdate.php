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
 * Class SelfUpdate.
 *
 * @package webisters
 */
class SelfUpdate extends Command
{
    protected string $name = 'update';
    protected string $description = 'Updates the global Webisters CLI to the latest version.';
    protected string $usage = 'update';

    public function run() : void
    {
        CLI::info('Updating Webisters through Composer...');

        $output = [];
        $exitCode = $this->executeComposerUpdate($this->getComposerUpdateCommand(), $output);

        if ($exitCode !== 0) {
            CLI::error('Webisters update failed with exit code ' . $exitCode . '.', null);

            if ($output !== []) {
                CLI::write('Composer output:');
                foreach ($output as $line) {
                    if (\trim($line) === '') {
                        continue;
                    }
                    CLI::write('  ' . $line);
                }
            }

            CLI::write('Run the Composer command manually or check your network/package source.');
            return;
        }

        CLI::success('Webisters is up to date.');
        CLI::write('Restart your terminal if the updated command is already on disk.');
    }

    /**
     * @param array<int, string> $output
     */
    protected function executeComposerUpdate(string $command, array &$output) : int
    {
        $exitCode = 1;
        \exec($command . ' 2>&1', $output, $exitCode);
        return $exitCode;
    }

    protected function getComposerUpdateCommand() : string
    {
        return 'composer global update webisters/webisters --no-interaction --prefer-dist --no-progress';
    }
}
