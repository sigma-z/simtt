<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   09.12.19
 */

namespace Test\Domain\Model;

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
    public function testConstruct(string $time, int $expectedHour, int $expectedMinute, string $expectedToString): void
    {
        $time = new Time($time);
        self::assertSame($expectedHour, $time->getHour());
        self::assertSame($expectedMinute, $time->getMinute());
        self::assertSame($expectedToString, (string)$time);
    }

    public function provideConstruct(): array
    {
        return [
            ['000', 0, 0, '00:00'],
            ['0:00', 0, 0, '00:00'],
            ['00:00', 0, 0, '00:00'],
            ['23:00', 23, 0, '23:00'],
            ['2300', 23, 0, '23:00'],
            ['203', 2, 3, '02:03'],
            ['23:59', 23, 59, '23:59'],
            ['0:59', 0, 59, '00:59'],
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

    public function testIsOlderThan(): void
    {
        $actual = (new Time('230'))->isOlderThan(new Time('300'));
        self::assertTrue($actual);
    }

    public function testIsNewThan(): void
    {
        $actual = (new Time('230'))->isNewerThan(new Time('200'));
        self::assertTrue($actual);
    }

    /**
     * @dataProvider provideCompare
     * @param string $timeA
     * @param string $timeB
     * @param int    $expected
     */
    public function testCompare(string $timeA, string $timeB, int $expected): void
    {
        $timeObjA = new Time($timeA);
        $timeObjB = new Time($timeB);
        self::assertSame($expected, $timeObjA->compare($timeObjB));
    }

    public function provideCompare(): array
    {
        return [
            ['000', '000', 0],
            ['101', '01:01', 0],
            ['101', '01:02', -1],
            ['102', '01:00', 1],
            ['2:00', '01:59', 1],
        ];
    }

    /**
     * @dataProvider provideDiff
     * @param string $timeA
     * @param string $timeB
     * @param int    $expectedHourDiff
     * @param int    $expectedMinuteDiff
     */
    public function testDiff(string $timeA, string $timeB, int $expectedHourDiff, int $expectedMinuteDiff): void
    {
        $timeObjA = new Time($timeA);
        $timeObjB = new Time($timeB);
        $dateInterval = $timeObjA->diff($timeObjB);
        self::assertSame($expectedHourDiff, $dateInterval->h);
        self::assertSame($expectedMinuteDiff, $dateInterval->i);
    }

    public function provideDiff(): array
    {
        return [
            ['0:00', '0:00', 0, 0],
            ['0:00', '0:05', 0, 5],
            ['1:00', '0:00', 1, 0],
            ['23:59', '0:00', 23, 59],
            ['0:00', '23:59', 23, 59],
        ];
    }

    /**
     * @dataProvider provideRoundBy
     * @param string $time
     * @param int    $precision
     * @param string $expectedTime
     */
    public function testRoundBy(string $time, int $precision, string $expectedTime): void
    {
        $time = new Time($time);
        $time->roundBy($precision);
        self::assertSame($expectedTime, (string)$time);
    }

    public function provideRoundBy(): array
    {
        return [
            ['11:10', -5, '11:10'],
            ['11:10', 0, '11:10'],
            ['11:10', 1, '11:10'],
            ['11:10', 5, '11:10'],
            ['11:11', 5, '11:10'],
            ['11:12', 5, '11:10'],
            ['11:13', 5, '11:15'],
            ['11:17', 5, '11:15'],
            ['11:17', 2, '11:18'],
            ['11:16', 2, '11:16'],
            ['11:16', 10, '11:20'],
            ['11:14', 10, '11:10'],
            ['11:14', 60, '11:00'],
            ['11:30', 60, '12:00'],
            ['11:58', 5, '12:00'],
            ['23:57', 5, '23:55'],
        ];
    }

    public function testRoundByOutOfRange(): void
    {
        $this->expectException(\RuntimeException::class);
        $time = new Time('23:58');
        $time->roundBy(5);
    }
}
