<?php declare(strict_types=1);
/*
 * This file is part of Webisters Command Line Tool.
 *
 * (c) Hafiz Muhammad Moaz <thewebisters@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Webisters\CLI;

use Framework\CLI\Console;
use Webisters\Commands\Check;
use Webisters\Commands\Completion;
use Webisters\Commands\Doctor;
use Webisters\Commands\Index;
use Webisters\Commands\ListCommand;
use Webisters\Commands\MakeController;
use Webisters\Commands\MakeModel;
use Webisters\Commands\MakeView;
use Webisters\Commands\NewApi;
use Webisters\Commands\NewApp;
use Webisters\Commands\NewOne;
use Webisters\Commands\NewSite;
use Webisters\Commands\RouteList;
use Webisters\Commands\SelfUpdate;
use Webisters\Commands\Setup;

final class Kernel
{
    /**
     * Maps the grouped "new <type>" alias to the corresponding registered
     * "new-<type>" command name.
     *
     * @var array<string, string>
     */
    private const NEW_ALIASES = [
        'app' => 'new-app',
        'api' => 'new-api',
        'one' => 'new-one',
        'site' => 'new-site',
    ];

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
        $console->addCommand(Check::class);
        $console->addCommand(Completion::class);
        $console->addCommand(Doctor::class);
        $console->addCommand(Index::class);
        $console->addCommand(ListCommand::class);
        $console->addCommand(Setup::class);
        $console->addCommand(RouteList::class);
        $console->addCommand(MakeView::class);
        $console->addCommand(MakeModel::class);
        $console->addCommand(MakeController::class);
        $console->addCommand(NewApp::class);
        $console->addCommand(NewApi::class);
        $console->addCommand(NewOne::class);
        $console->addCommand(NewSite::class);
        $console->addCommand(SelfUpdate::class);
        $console->run();

        return 0;
    }

    /**
     * Rewrites the grouped alias form of the scaffolding commands into the
     * flat form the Console actually registers.
     *
     * The Console only knows the flat commands (`new-app`, `new-api`,
     * `new-one`, `new-site`). To also accept the friendlier grouped form
     * (`webisters new app <name>`), this method rewrites the argv in place
     * before the Console sees it. Every other invocation is passed through
     * untouched, so calling the flat commands directly still works.
     *
     * Paths:
     *   - `$argv[1]` is not `new`           -> returned unchanged (passthrough)
     *   - type is missing or unknown        -> usage printed, returns null
     *   - name is missing or blank          -> usage printed, returns null
     *   - valid `new <type> <name> [...]`   -> rewritten to `new-<type> <name> [...]`
     *
     * @param array<int, string> $argv
     *
     * @return array<int, string>|null the rewritten argv, or null when the
     *                                 grouped form was malformed
     */
    private function normalizeArgv(array $argv) : ?array
    {
        $command = $argv[1] ?? null;
        if ($command !== 'new') {
            return $argv;
        }

        $type = $argv[2] ?? null;
        if ($type === null || !isset(self::NEW_ALIASES[$type])) {
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
            self::NEW_ALIASES[$type],
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
