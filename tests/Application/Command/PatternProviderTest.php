<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   14.01.20
 */

namespace Test\Application\Command;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Simtt\Application\Command\PatternProvider;

class PatternProviderTest extends TestCase
{

    #[DataProvider('provideIsTime')]
    public function testIsTime(string $time, bool $matches): void
    {
        self::assertSame($matches, PatternProvider::isTime($time));
    }

    public static function provideIsTime(): array
    {
        return [
            ['', false],
            ['000', true],
            ['0:00', true],
            ['900', true],
            ['0900', true],
            ['2300', true],
            ['12:00', true],
            ['12|00', false],
            ['123|654', false],
        ];
    }
}
