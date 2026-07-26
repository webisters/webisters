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
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class TestCase.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected Console $console;
    /**
     * @var class-string<\Framework\CLI\Command>
     */
    protected string $command;

    protected function setUp() : void
    {
        $this->console = new Console();
        $this->console->addCommand($this->command);
    }

    protected function deleteDirectory(string $directory) : void
    {
        if (!\is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @\rmdir((string) $item);
                continue;
            }

            @\unlink((string) $item);
        }

        @\rmdir($directory);
    }
}
