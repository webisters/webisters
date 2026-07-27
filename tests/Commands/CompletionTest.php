<?php declare(strict_types=1);
/*
 * This file is part of Webisters Command Line Tool.
 *
 * (c) Hafiz Muhammad Moaz <thewebisters@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\Commands;

use Framework\CLI\Console;
use Framework\CLI\Streams\Stdout;
use Webisters\Commands\Completion;
use Webisters\Commands\NewApp;

/**
 * Class CompletionTest.
 */
final class CompletionTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
        // Stdout is a process-global capture buffer; clear it so each test
        // only sees the output it produced.
        Stdout::init();
        Stdout::reset();
    }

    public function testBashCompletionIsDefaultAndListsCommands() : void
    {
        $console = new Console();
        $console->addCommand(Completion::class);
        $console->addCommand(NewApp::class);

        $console->exec('completion');

        $output = Stdout::getContents();
        self::assertStringContainsString('_webisters_complete()', $output);
        self::assertStringContainsString('complete -F _webisters_complete webisters', $output);
        // Command names are read from the live console registration.
        self::assertStringContainsString('new-app', $output);
        // The grouped "new <type>" subtypes are offered after "new".
        self::assertStringContainsString('app api one site', $output);
        // The completion command excludes itself from the suggestions.
        self::assertStringNotContainsString('"completion', $output);
    }

    public function testBashCompletionRequestedExplicitly() : void
    {
        $console = new Console();
        $console->addCommand(Completion::class);

        $console->exec('completion bash');

        self::assertStringContainsString('_webisters_complete()', Stdout::getContents());
    }

    public function testZshCompletionEmitsCompdef() : void
    {
        $console = new Console();
        $console->addCommand(Completion::class);
        $console->addCommand(NewApp::class);

        $console->exec('completion zsh');

        $output = Stdout::getContents();
        self::assertStringContainsString('#compdef webisters', $output);
        self::assertStringContainsString('_webisters()', $output);
        self::assertStringContainsString('new-app', $output);
        self::assertStringContainsString('app api one site', $output);
    }

    public function testUnsupportedShellIsRejected() : void
    {
        $console = new Console();
        $console->addCommand(Completion::class);

        $console->exec('completion fish');

        // Nothing valid should be written to stdout for an unknown shell.
        self::assertStringNotContainsString('_webisters', Stdout::getContents());
    }
}
