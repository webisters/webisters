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
use Webisters\Commands\NewApi;

/**
 * Class NewApiTest.
 */
final class NewApiTest extends TestCase
{
    protected string $command = NewApi::class;

    public function testNewApi() : void
    {
        $dir = \sys_get_temp_dir() . '/webisters-api';
        $this->deleteDirectory($dir);
        Stdout::init();
        $this->console->exec('new-api --no-install ' . $dir);
        self::assertStringContainsString(
            'API Project structure created at "' . $dir . '"',
            Stdout::getContents()
        );
        $this->deleteDirectory($dir);
    }
}
