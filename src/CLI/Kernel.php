<?php declare(strict_types=1);

namespace Webisters\CLI;

use Framework\CLI\Console;
use Webisters\Commands\Index;
use Webisters\Commands\NewApp;
use Webisters\Commands\NewApi;
use Webisters\Commands\NewOne;
use Webisters\Commands\NewSite;

final class Kernel
{
    /**
     * @param array<int, string> $argv
     */
    public function run(array $argv) : int
    {
        $argv = $this->normalizeArgv($argv);
        if ($argv === null) {
            return 1;
        }

        // The Console reads from global $argv in its constructor.
        $GLOBALS['argv'] = $argv;

        $console = new Console();
        $console->addCommand(Index::class);
        $console->addCommand(NewApp::class);
        $console->addCommand(NewApi::class);
        $console->addCommand(NewOne::class);
        $console->addCommand(NewSite::class);
        $console->run();

        return 0;
    }

    /**
     * @param array<int, string> $argv
     *
     * @return array<int, string>|null
     */
    private function normalizeArgv(array $argv) : ?array
    {
        $command = $argv[1] ?? null;
        if ($command !== 'new') {
            return $argv;
        }

        $type = $argv[2] ?? null;
        if ($type !== 'app' && $type !== 'api' && $type !== 'one' && $type !== 'site') {
            $this->printUsage();
            return null;
        }

        $name = $argv[3] ?? null;
        if ($name === null || \trim($name) === '') {
            $this->printUsage();
            return null;
        }

        $normalized = [
            $argv[0] ?? 'webisters',
            $type === 'app'
                ? 'new-app'
                : ($type === 'api'
                    ? 'new-api'
                    : ($type === 'one' ? 'new-one' : 'new-site')),
            $name,
        ];

        foreach (\array_slice($argv, 4) as $extra) {
            $normalized[] = $extra;
        }

        return $normalized;
    }

    private function printUsage() : void
    {
        \fwrite(\STDERR, 'Usage:' . \PHP_EOL);
        \fwrite(\STDERR, '  webisters new app <name>' . \PHP_EOL);
        \fwrite(\STDERR, '  webisters new api <name>' . \PHP_EOL);
        \fwrite(\STDERR, '  webisters new one <name>' . \PHP_EOL);
        \fwrite(\STDERR, '  webisters new site <name>' . \PHP_EOL);
    }
}
