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

/**
 * Class ListCommand.
 *
 * @package webisters
 */
class ListCommand extends Command
{
    protected string $name = 'list';
    protected string $description = 'Lists available project generators.';
    protected string $usage = 'list';
    protected string $group = 'Generators';

    public function run() : void
    {
        $newCommands = $this->findGeneratorCommands('new-');
        $makeCommands = $this->findGeneratorCommands('make:');

        if ($newCommands === [] && $makeCommands === []) {
            CLI::info('No generator commands found.');
            return;
        }

        CLI::success('Available generators');
        CLI::newLine();

        $this->printSection('new-*', $newCommands);
        CLI::newLine();
        $this->printSection('make:*', $makeCommands);
    }

    /**
     * @return array<string, Command>
     */
    protected function findGeneratorCommands(string $prefix) : array
    {
        $result = [];

        foreach ($this->console->getCommands() as $name => $command) {
            if (!\str_starts_with($name, $prefix)) {
                continue;
            }

            $result[$name] = $command;
        }

        \ksort($result);

        return $result;
    }

    /**
     * @param array<string, Command> $commands
     */
    protected function printSection(string $title, array $commands) : void
    {
        CLI::write($title . ':', ForegroundColor::brightYellow);

        if ($commands === []) {
            CLI::write('  (none)');
            return;
        }

        foreach ($commands as $name => $command) {
            CLI::write(
                '  ' . CLI::style($name, ForegroundColor::green)
                . '  ' . $this->normalizeDescription($command->getDescription())
            );
        }
    }

    protected function normalizeDescription(string $description) : string
    {
        $description = \trim($description);
        if ($description === '') {
            return '-';
        }

        if (!\str_ends_with($description, '.')) {
            $description .= '.';
        }

        return $description;
    }
}
