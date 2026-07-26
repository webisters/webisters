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

use Framework\CLI\Streams\Stdout;
use Webisters\Commands\NewApp;

/**
 * Class NewAppTest.
 */
final class NewAppTest extends TestCase
{
    protected string $command = NewApp::class;

    public function testNewApp() : void
    {
        $dir = \sys_get_temp_dir() . '/webisters-app';
        $this->deleteDirectory($dir);
        Stdout::init();
        $this->console->exec('new-app --no-install ' . $dir);
        self::assertStringContainsString(
            'App Project structure created at "' . $dir . '"',
            Stdout::getContents()
        );
        $this->deleteDirectory($dir);
    }
}
