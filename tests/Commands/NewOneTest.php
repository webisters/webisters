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
use Webisters\Commands\NewOne;

/**
 * Class NewOneTest.
 */
final class NewOneTest extends TestCase
{
    protected string $command = NewOne::class;

    public function testNewApp() : void
    {
        $dir = \sys_get_temp_dir() . '/webisters-one';
        $this->deleteDirectory($dir);
        Stdout::init();
        $this->console->exec('new-one --no-install ' . $dir);
        self::assertStringContainsString(
            'One Project structure created at "' . $dir . '"',
            Stdout::getContents()
        );
        $this->deleteDirectory($dir);
    }
}
