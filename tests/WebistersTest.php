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

use Framework\CLI\Streams\Stdout;
use PHPUnit\Framework\TestCase;
use Webisters;

/**
 * Class WebistersTest.
 */
final class WebistersTest extends TestCase
{
    public function testWebisters() : void
    {
        Stdout::init();
        // Stdout is a process-wide buffer that other tests may have written to,
        // so capture the baseline instead of assuming it starts empty.
        $before = Stdout::getContents();
        require __DIR__ . '/../src/webisters.php';
        $output = Stdout::getContents();
        self::assertNotSame($before, $output);
        self::assertStringContainsString(
            'Webisters ' . Webisters::VERSION,
            $output
        );
    }
}
