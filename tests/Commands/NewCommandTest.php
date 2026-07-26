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

    public function testDryRunPrintsPlannedActionsWithoutWritingFiles() : void
    {
        $command = new NewCommandHarness();
        $command->setConsoleForTest(new ConsoleHarness(['dry-run' => true]));
        $command->setDirectoryForTest(
            \sys_get_temp_dir() . '/webisters-dry-run-' . \uniqid('', true)
        );

        Stderr::init();
        Stdout::init();

        $command->createForTest('app', 'App Project');

        self::assertStringContainsString('Dry run: no files will be written.', Stdout::getContents());
        self::assertStringContainsString('Would copy the template', Stdout::getContents());
        self::assertFalse($command->wasHeaderShownForTest());
        self::assertFalse($command->wasRequiredExtensionsCheckedForTest());
        self::assertFalse($command->wasComposerInstallTriggeredForTest());
        self::assertFalse($command->wasProjectFilesWrittenForTest());
    }

    public function testDryRunPreviewsPlanWhenTargetDirectoryAlreadyExists() : void
    {
        $existing = \sys_get_temp_dir() . '/webisters-dry-run-existing-' . \uniqid('', true);
        \mkdir($existing, 0755, true);

        try {
            $command = new NewCommandHarness();
            $command->setConsoleForTest(new ConsoleHarness(['dry-run' => true]));
            $command->setDirectoryForTest($existing);

            Stderr::init();
            Stdout::init();

            $command->createForTest('app', 'App Project');

            self::assertStringContainsString('Dry run: no files will be written.', Stdout::getContents());
            self::assertStringContainsString('already exists', Stdout::getContents());
            self::assertStringNotContainsString('already exists', Stderr::getContents());
            self::assertFalse($command->wasProjectFilesWrittenForTest());
        } finally {
            @\rmdir($existing);
        }
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
    private string $directory = '';
    private bool $headerShownForTest = false;
    private bool $requiredExtensionsChecked = false;
    private bool $composerInstallTriggered = false;
    private bool $projectFilesWritten = false;

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

    public function setDirectoryForTest(string $directory) : void
    {
        $this->directory = $directory;
    }

    public function setConsoleForTest(Console $console) : void
    {
        $this->setConsole($console);
    }

    public function createForTest(string $package, string $name) : void
    {
        $this->create($package, $name);
    }

    public function wasHeaderShownForTest() : bool
    {
        return $this->headerShownForTest;
    }

    public function wasRequiredExtensionsCheckedForTest() : bool
    {
        return $this->requiredExtensionsChecked;
    }

    public function wasComposerInstallTriggeredForTest() : bool
    {
        return $this->composerInstallTriggered;
    }

    public function wasProjectFilesWrittenForTest() : bool
    {
        return $this->projectFilesWritten;
    }

    protected function showHeaderOnce() : void
    {
        $this->headerShownForTest = true;
    }

    protected function ensureRequiredExtensions() : bool
    {
        $this->requiredExtensionsChecked = true;
        return parent::ensureRequiredExtensions();
    }

    protected function ensureDirectoryExists(string $directory) : void
    {
        $this->projectFilesWritten = true;
        parent::ensureDirectoryExists($directory);
    }

    protected function copyDir(string $source, string $directory) : void
    {
        $this->projectFilesWritten = true;
        parent::copyDir($source, $directory);
    }

    protected function ensureProjectCliFile(string $directory) : void
    {
        $this->projectFilesWritten = true;
        parent::ensureProjectCliFile($directory);
    }

    protected function updateComposerMetadata(string $directory, string $projectName, string $projectDescription) : void
    {
        $this->projectFilesWritten = true;
        parent::updateComposerMetadata($directory, $projectName, $projectDescription);
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

    protected function runComposerInstall(string $directory) : void
    {
        $this->composerInstallTriggered = true;
        parent::runComposerInstall($directory);
    }

    protected function getDirectoryPath() : string
    {
        return $this->directory;
    }

    protected function resolveDirectoryPath() : string
    {
        return $this->directory;
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
