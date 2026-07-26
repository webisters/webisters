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
use Webisters\Commands\Check;
use Webisters\Commands\Doctor;

/**
 * Class CheckTest.
 */
final class CheckTest extends \PHPUnit\Framework\TestCase
{
    public function testCheckReportsEnvironmentStatus() : void
    {
        $command = new CheckHarness();
        $command->setPhpVersionForTest('8.2.99');
        $command->setExtensionsForTest([
            'intl' => true,
            'sodium' => false,
            'zip' => true,
        ]);
        $command->setComposerStatusForTest(0, ['Composer version 2.8.0']);

        Stdout::init();
        $command->run();

        $output = Stdout::getContents();
        self::assertStringContainsString('Webisters environment check', $output);
        self::assertStringContainsString('PHP version: 8.2.99', $output);
        self::assertStringContainsString('intl: enabled', $output);
        self::assertStringContainsString('sodium: missing', $output);
        self::assertStringContainsString('Composer: Composer version 2.8.0', $output);
    }

    public function testDoctorCommandIsRegistered() : void
    {
        $console = new Console();
        $check = new CheckHarness();
        $check->setPhpVersionForTest('8.2.99');
        $check->setExtensionsForTest(['intl' => true]);
        $check->setComposerStatusForTest(0, ['Composer version 2.8.0']);
        $doctor = new DoctorHarness();
        $doctor->setPhpVersionForTest('8.2.99');
        $doctor->setExtensionsForTest(['intl' => true]);
        $doctor->setComposerStatusForTest(0, ['Composer version 2.8.0']);

        $console->addCommand($check);
        $console->addCommand($doctor);

        Stdout::init();
        $console->exec('doctor');

        self::assertStringContainsString('PHP version: 8.2.99', Stdout::getContents());
    }
}

final class CheckHarness extends Check
{
    /**
     * @var array<string, bool>
     */
    private array $extensions = [];

    /**
     * @var array<int, string>
     */
    private array $composerOutput = [];

    private int $composerExitCode = 0;

    private string $phpVersion = '8.2.0';

    public function setPhpVersionForTest(string $phpVersion) : void
    {
        $this->phpVersion = $phpVersion;
    }

    /**
     * @param array<string, bool> $extensions
     */
    public function setExtensionsForTest(array $extensions) : void
    {
        $this->extensions = $extensions;
    }

    /**
     * @param array<int, string> $output
     */
    public function setComposerStatusForTest(int $exitCode, array $output) : void
    {
        $this->composerExitCode = $exitCode;
        $this->composerOutput = $output;
    }

    protected function getPhpVersion() : string
    {
        return $this->phpVersion;
    }

    protected function isExtensionLoaded(string $extension) : bool
    {
        return $this->extensions[$extension] ?? false;
    }

    /**
     * @param array<int, string> $output
     */
    protected function executeComposerVersion(array &$output) : int
    {
        $output = $this->composerOutput;
        return $this->composerExitCode;
    }
}

final class DoctorHarness extends Doctor
{
    use CheckHarnessMethods;
}

trait CheckHarnessMethods
{
    /**
     * @var array<string, bool>
     */
    private array $extensions = [];

    /**
     * @var array<int, string>
     */
    private array $composerOutput = [];

    private int $composerExitCode = 0;

    private string $phpVersion = '8.2.0';

    public function setPhpVersionForTest(string $phpVersion) : void
    {
        $this->phpVersion = $phpVersion;
    }

    /**
     * @param array<string, bool> $extensions
     */
    public function setExtensionsForTest(array $extensions) : void
    {
        $this->extensions = $extensions;
    }

    /**
     * @param array<int, string> $output
     */
    public function setComposerStatusForTest(int $exitCode, array $output) : void
    {
        $this->composerExitCode = $exitCode;
        $this->composerOutput = $output;
    }

    protected function getPhpVersion() : string
    {
        return $this->phpVersion;
    }

    protected function isExtensionLoaded(string $extension) : bool
    {
        return $this->extensions[$extension] ?? false;
    }

    /**
     * @param array<int, string> $output
     */
    protected function executeComposerVersion(array &$output) : int
    {
        $output = $this->composerOutput;
        return $this->composerExitCode;
    }
}
