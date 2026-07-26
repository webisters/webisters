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
use Framework\CLI\Streams\Stderr;
use Framework\CLI\Streams\Stdout;
use Webisters\Commands\NewCommand;

/**
 * Class NewCommandTest.
 */
final class NewCommandTest extends \PHPUnit\Framework\TestCase
{
    public function testEnsureRequiredExtensionsPassesWhenNoRequirements() : void
    {
        $command = new NewCommandHarness();
        $command->setRequiredExtensionsForTest([]);

        self::assertTrue($command->ensureRequiredExtensionsForTest());
    }

    public function testEnsureRequiredExtensionsReportsMissingOnes() : void
    {
        $command = new NewCommandHarness();
        $command->setRequiredExtensionsForTest(['webisters_missing_extension']);

        Stderr::init();
        self::assertFalse($command->ensureRequiredExtensionsForTest());
        self::assertStringContainsString(
            'Missing required PHP extensions: webisters_missing_extension',
            Stderr::getContents()
        );
    }

    public function testCreateProjectWithComposerShowsComposerOutputOnFailure() : void
    {
        $command = new NewCommandHarness();
        $command->setComposerCreateProjectResult(
            2,
            ['Could not resolve host: repo.packagist.org', 'Network request failed']
        );

        Stderr::init();
        Stdout::init();

        $target = \sys_get_temp_dir() . '/webisters-create-project-fail-' . \uniqid('', true);
        self::assertFalse($command->createProjectWithComposerForTest('webisters/app', $target));

        self::assertStringContainsString(
            'Composer create-project failed with exit code 2.',
            Stderr::getContents()
        );
        self::assertStringContainsString('Composer output:', Stdout::getContents());
        self::assertStringContainsString('Could not resolve host', Stdout::getContents());
    }

    public function testShouldRunComposerInstallHonorsNoInstallOption() : void
    {
        $command = new NewCommandHarness();
        $command->setInteractiveInputForTest(false);
        $command->setConsoleForTest(new ConsoleHarness(['no-install' => true]));

        self::assertFalse($command->shouldRunComposerInstallForTest());
    }

    public function testShouldRunComposerInstallHonorsWithInstallOption() : void
    {
        $command = new NewCommandHarness();
        $command->setInteractiveInputForTest(false);
        $command->setConsoleForTest(new ConsoleHarness(['with-install' => true]));

        self::assertTrue($command->shouldRunComposerInstallForTest());
    }
}

final class ConsoleHarness extends Console
{
    /**
     * @param array<string, bool|string> $options
     */
    public function __construct(private array $testOptions = [])
    {
        parent::__construct();
    }

    public function getOption(string $option) : bool | string | null
    {
        return $this->testOptions[$option] ?? null;
    }
}

final class NewCommandHarness extends NewCommand
{
    protected string $name = 'new-harness';
    protected string $description = 'Test harness';
    protected string $usage = 'new-harness';

    private int $composerExitCode = 0;
    private bool $interactive = false;

    /**
     * @var array<int, string>
     */
    private array $composerOutput = [];

    public function run() : void
    {
    }

    public function setInteractiveInputForTest(bool $interactive) : void
    {
        $this->interactive = $interactive;
    }

    public function setConsoleForTest(Console $console) : void
    {
        $this->setConsole($console);
    }

    public function shouldRunComposerInstallForTest() : bool
    {
        return $this->shouldRunComposerInstall();
    }

    /**
     * @param array<int, string> $extensions
     */
    public function setRequiredExtensionsForTest(array $extensions) : void
    {
        $this->requiredExtensions = $extensions;
    }

    public function ensureRequiredExtensionsForTest() : bool
    {
        return $this->ensureRequiredExtensions();
    }

    /**
     * @param array<int, string> $output
     */
    public function setComposerCreateProjectResult(int $exitCode, array $output) : void
    {
        $this->composerExitCode = $exitCode;
        $this->composerOutput = $output;
    }

    public function createProjectWithComposerForTest(string $package, string $directory) : bool
    {
        return $this->createProjectWithComposer($package, $directory);
    }

    /**
     * @param array<int, string> $output
     */
    protected function executeComposerCreateProject(string $command, array &$output) : int
    {
        $output = $this->composerOutput;
        return $this->composerExitCode;
    }

    protected function isInteractiveInput() : bool
    {
        return $this->interactive;
    }
}
