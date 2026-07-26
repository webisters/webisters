<?php
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
use Webisters\Commands\ListCommand;
use Webisters\Commands\MakeController;
use Webisters\Commands\MakeModel;
use Webisters\Commands\NewApi;
use Webisters\Commands\NewApp;

/**
 * Class ListCommandTest.
 */
final class ListCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testListShowsAvailableGenerators() : void
    {
        $console = new Console();
        $console->addCommand(ListCommand::class);
        $console->addCommand(NewApp::class);
        $console->addCommand(NewApi::class);
        $console->addCommand(MakeModel::class);
        $console->addCommand(MakeController::class);

        Stdout::init();
        $console->exec('list');

        $output = Stdout::getContents();
        self::assertStringContainsString('Available generators', $output);
        self::assertStringContainsString('new-*:', $output);
        self::assertStringContainsString('new-app', $output);
        self::assertStringContainsString('new-api', $output);
        self::assertStringContainsString('make:*', $output);
        self::assertStringContainsString('make:model', $output);
        self::assertStringContainsString('make:controller', $output);
    }
}
