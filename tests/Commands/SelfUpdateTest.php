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

use Framework\CLI\Streams\Stderr;
use Framework\CLI\Streams\Stdout;
use Webisters\Commands\SelfUpdate;

/**
 * Class SelfUpdateTest.
 */
final class SelfUpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testSelfUpdateSuccess() : void
    {
        $command = new SelfUpdateHarness();
        $command->setResult(0, ['Updated 1 package']);

        Stdout::init();
        $command->run();

        self::assertStringContainsString('Updating Webisters through Composer...', Stdout::getContents());
        self::assertStringContainsString('Webisters is up to date.', Stdout::getContents());
    }

    public function testSelfUpdateFailureShowsComposerOutput() : void
    {
        $command = new SelfUpdateHarness();
        $command->setResult(1, ['Could not reach packagist.org']);

        Stdout::init();
        Stderr::init();
        $command->run();

        self::assertStringContainsString('Webisters update failed with exit code 1.', Stderr::getContents());
        self::assertStringContainsString('Composer output:', Stdout::getContents());
        self::assertStringContainsString('Could not reach packagist.org', Stdout::getContents());
    }
}

final class SelfUpdateHarness extends SelfUpdate
{
    /**
     * @var array<int, string>
     */
    private array $output = [];

    private int $exitCode = 0;

    /**
     * @param array<int, string> $output
     */
    public function setResult(int $exitCode, array $output) : void
    {
        $this->exitCode = $exitCode;
        $this->output = $output;
    }

    /**
     * @param array<int, string> $output
     */
    protected function executeComposerUpdate(string $command, array &$output) : int
    {
        $output = $this->output;
        return $this->exitCode;
    }
}
