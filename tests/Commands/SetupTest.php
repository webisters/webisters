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

use Framework\CLI\Streams\Stdout;
use Webisters\Commands\Setup;

/**
 * Class SetupTest.
 */
final class SetupTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
        // Stdout is a process-global capture buffer; clear it so each test
        // only sees the output it produced.
        Stdout::init();
        Stdout::reset();
    }

    public function testWindowsAddedPathPrintsPerShellRestartAndVerification() : void
    {
        $binDir = 'C:/Users/dev/AppData/Roaming/Composer/vendor/bin';

        $command = new SetupHarness();
        $command->setWindowsForTest(true);
        $command->setBinDirForTest($binDir);
        $command->setPsResultForTest('added');

        $command->run();

        $output = Stdout::getContents();
        self::assertStringContainsString('Added Composer global bin directory to your user PATH.', $output);
        self::assertStringContainsString('Open a NEW terminal for the change to take effect.', $output);
        self::assertStringContainsString('Command Prompt, PowerShell, and Git Bash', $output);
        self::assertStringContainsString('webisters --version', $output);
        self::assertStringContainsString('where webisters', $output);
        // The bin dir must flow into the real PowerShell command that gets executed.
        self::assertStringContainsString('vendor', (string) $command->getCapturedCommand());
    }

    public function testWindowsAlreadyConfiguredPrintsGuidance() : void
    {
        $command = new SetupHarness();
        $command->setWindowsForTest(true);
        $command->setBinDirForTest('C:/Users/dev/AppData/Roaming/Composer/vendor/bin');
        $command->setPsResultForTest('already');

        $command->run();

        $output = Stdout::getContents();
        self::assertStringContainsString('PATH is already configured.', $output);
        self::assertStringContainsString('Command Prompt, PowerShell, and Git Bash', $output);
        self::assertStringContainsString('webisters --version', $output);
    }

    public function testBinDirFallbackToAppData() : void
    {
        $original = \getenv('APPDATA');
        \putenv('APPDATA=C:\\Users\\dev\\AppData\\Roaming');

        try {
            $command = new SetupHarness();
            $command->setWindowsForTest(true);
            // Force the composer lookup to fail so the APPDATA fallback runs.
            $command->setBinDirForTest(null);
            $command->setPsResultForTest('added');

            $command->run();

            $output = Stdout::getContents();
            self::assertStringContainsString('Composer', $output);
            self::assertStringContainsString('vendor', $output);
            self::assertStringContainsString('bin', $output);
        } finally {
            if ($original === false) {
                \putenv('APPDATA');
            } else {
                \putenv('APPDATA=' . $original);
            }
        }
    }

    public function testNonWindowsPrintsManualInstructions() : void
    {
        $command = new SetupHarness();
        $command->setWindowsForTest(false);
        $command->setBinDirForTest('/home/dev/.composer/vendor/bin');

        $command->run();

        $output = Stdout::getContents();
        self::assertStringContainsString('Automatic PATH setup is currently supported on Windows only.', $output);
        self::assertStringContainsString('Manual fallback (Linux/macOS):', $output);
        self::assertStringContainsString('/home/dev/.composer/vendor/bin', $output);
    }
}

final class SetupHarness extends Setup
{
    private ?string $binDir = null;

    private string $psResult = 'added';

    private bool $windows = true;

    private ?string $capturedCommand = null;

    public function setBinDirForTest(?string $binDir) : void
    {
        $this->binDir = $binDir;
    }

    public function setPsResultForTest(string $result) : void
    {
        $this->psResult = $result;
    }

    public function setWindowsForTest(bool $windows) : void
    {
        $this->windows = $windows;
    }

    public function getCapturedCommand() : ?string
    {
        return $this->capturedCommand;
    }

    protected function isWindows() : bool
    {
        return $this->windows;
    }

    protected function executeComposerBinDir() : ?string
    {
        return $this->binDir;
    }

    protected function executePowerShellSetPath(string $command) : ?string
    {
        $this->capturedCommand = $command;
        return $this->psResult;
    }
}
