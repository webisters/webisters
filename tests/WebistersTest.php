<?php
/*
 * This file is part of Webisters Command Line Tool.
 *
 * (c) Hafiz Muhammad Moaz <thewebisters@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests;

use Webisters;
use Framework\CLI\Streams\Stdout;
use PHPUnit\Framework\TestCase;

/**
 * Class WebistersTest.
 */
final class WebistersTest extends TestCase
{
    public function testWebisters() : void
    {
        Stdout::init();
        self::assertSame('', Stdout::getContents());
        require __DIR__ . '/../src/webisters.php';
        self::assertNotSame('', Stdout::getContents());
        self::assertStringContainsString(
            'Webisters ' . Webisters::VERSION,
            Stdout::getContents()
        );
    }
}
