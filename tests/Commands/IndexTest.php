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
use Webisters;

/**
 * Class IndexTest.
 */
final class IndexTest extends TestCase
{
    protected string $command = Webisters\Commands\Index::class;

    public function testIndex() : void
    {
        Stdout::init();
        $this->console->exec('index');
        self::assertStringContainsString(
            'Webisters ' . Webisters::VERSION,
            Stdout::getContents()
        );
    }
}
