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

    public function testNormalizeArgvRejectsUnknownType() : void
    {
        $kernel = new Kernel();

        self::assertNull(
            $this->normalizeArgv($kernel, ['webisters', 'new', 'unknown', 'demo'])
        );
    }

    public function testNormalizeArgvRejectsMissingName() : void
    {
        $kernel = new Kernel();

        self::assertNull(
            $this->normalizeArgv($kernel, ['webisters', 'new', 'app'])
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
}
