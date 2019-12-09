<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   09.12.19
 */

namespace Domain\Model;

use PHPUnit\Framework\TestCase;
use Simtt\Domain\Model\Time;

class TimeTest extends TestCase
{

    /**
     * @dataProvider provideConstruct
     * @param string $time
     * @param int    $expectedHour
     * @param int    $expectedMinute
     */
    public function testConstruct(string $time, int $expectedHour, int $expectedMinute): void
    {
        $time = new Time($time);
        self::assertSame($expectedHour, $time->getHour());
        self::assertSame($expectedMinute, $time->getMinute());
    }

    public function provideConstruct(): array
    {
        return [
            ['000', 0, 0],
            ['0:00', 0, 0],
            ['00:00', 0, 0],
            ['23:00', 23, 0],
            ['2300', 23, 00],
            ['230', 2, 30],
            ['23:59', 23, 59],
            ['0:59', 0, 59],
        ];
    }

    /**
     * @dataProvider provideConstructThrowsException
     * @param string $time
     */
    public function testConstructThrowsException(string $time): void
    {
        $this->expectException(\RuntimeException::class);
        new Time($time);
    }

    public function provideConstructThrowsException(): array
    {
        return [
            ['abcd'],
            ['25:00'],
            ['00:60'],
            ['00'],
            ['00::00'],
            ['3000'],
            ['060'],
        ];
    }
}
