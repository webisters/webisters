<?php declare(strict_types=1);
/*
 * This file is part of Webisters Command Line Tool.
 *
 * (c) Hafiz Muhammad Moaz <thewebisters@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Tests\CLI;

use Webisters\CLI\Kernel;

/**
 * Class KernelTest.
 */
final class KernelTest extends \PHPUnit\Framework\TestCase
{
    public function testNormalizeArgvGroupedForm() : void
    {
        $kernel = new Kernel();

        $normalized = $this->normalizeArgv(
            $kernel,
            ['webisters', 'new', 'app', 'demo-app', '--dry-run']
        );

        self::assertSame(
            ['webisters', 'new-app', 'demo-app', '--dry-run'],
            $normalized
        );
    }

    /**
     * @dataProvider groupedTypeProvider
     */
    public function testNormalizeArgvMapsEveryGroupedType(string $type, string $alias) : void
    {
        $kernel = new Kernel();

        $normalized = $this->normalizeArgv(
            $kernel,
            ['webisters', 'new', $type, 'demo']
        );

        self::assertSame(['webisters', $alias, 'demo'], $normalized);
    }

    public function testNormalizeArgvPassesThroughNonNewCommand() : void
    {
        $kernel = new Kernel();

        $argv = ['webisters', 'doctor', '--verbose'];

        self::assertSame($argv, $this->normalizeArgv($kernel, $argv));
    }

    public function testNormalizeArgvPassesThroughFlatNewCommand() : void
    {
        $kernel = new Kernel();

        $argv = ['webisters', 'new-app', 'demo', '--dry-run'];

        self::assertSame($argv, $this->normalizeArgv($kernel, $argv));
    }

    public function testNormalizeArgvPassesThroughWithNoArguments() : void
    {
        $kernel = new Kernel();

        $argv = ['webisters'];

        self::assertSame($argv, $this->normalizeArgv($kernel, $argv));
    }

    public function testNormalizeArgvPreservesExtraArguments() : void
    {
        $kernel = new Kernel();

        $normalized = $this->normalizeArgv(
            $kernel,
            ['webisters', 'new', 'site', 'demo', '--dry-run', '--force']
        );

        self::assertSame(
            ['webisters', 'new-site', 'demo', '--dry-run', '--force'],
            $normalized
        );
    }

    public function testNormalizeArgvRejectsUnknownType() : void
    {
        $kernel = new Kernel();

        self::assertNull(
            $this->normalizeArgv($kernel, ['webisters', 'new', 'unknown', 'demo'])
        );
    }

    public function testNormalizeArgvRejectsMissingType() : void
    {
        $kernel = new Kernel();

        self::assertNull(
            $this->normalizeArgv($kernel, ['webisters', 'new'])
        );
    }

    public function testNormalizeArgvRejectsMissingName() : void
    {
        $kernel = new Kernel();

        self::assertNull(
            $this->normalizeArgv($kernel, ['webisters', 'new', 'app'])
        );
    }

    public function testNormalizeArgvRejectsBlankName() : void
    {
        $kernel = new Kernel();

        self::assertNull(
            $this->normalizeArgv($kernel, ['webisters', 'new', 'app', '   '])
        );
    }

    /**
     * @param array<int, string> $argv
     *
     * @return array<int, string>|null
     */
    private function normalizeArgv(Kernel $kernel, array $argv) : ?array
    {
        $method = new \ReflectionMethod(Kernel::class, 'normalizeArgv');
        $method->setAccessible(true);

        /** @var array<int, string>|null $normalized */
        return $method->invoke($kernel, $argv);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function groupedTypeProvider() : array
    {
        return [
            'app' => ['app', 'new-app'],
            'api' => ['api', 'new-api'],
            'one' => ['one', 'new-one'],
            'site' => ['site', 'new-site'],
        ];
    }
}
