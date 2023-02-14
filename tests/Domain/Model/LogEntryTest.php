<?php
declare(strict_types=1);

namespace Test\Domain\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;
use Test\Helper\LogEntryCreator;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogEntryTest extends TestCase
{
    #[DataProvider('provideToString')]
    public function testToString(string $startTime, string $stopTime, string $taskName, string $comment, string $expected): void
    {
        $logEntry = LogEntryCreator::create($startTime, $stopTime, $taskName, $comment);
        self::assertEquals($expected, (string)$logEntry);
    }

    public static function provideToString(): array
    {
        return [
            ['900', '', 'task', 'comment', '09:00;     ;"task";"comment"'],
            ['900', '1000', 'task', 'comment', '09:00;10:00;"task";"comment"'],
            ['900', '1000', '', '', '09:00;10:00;"";""'],
            ['900', '1000', '', 'comment', '09:00;10:00;"";"comment"'],
            ['900', '1000', 'task', '', '09:00;10:00;"task";""'],
        ];
    }

    #[DataProvider('provideFromString')]
    public function testFromString(
        string $raw,
        string $expectedStart,
        string $expectedStop,
        string $expectedTask,
        string $expectedComment
    ): void
    {
        $logEntry = LogEntry::fromString($raw);
        self::assertSame($expectedStart, (string)$logEntry->startTime);
        self::assertSame($expectedStop, (string)$logEntry->stopTime);
        self::assertSame($expectedTask, $logEntry->task);
        self::assertSame($expectedComment, $logEntry->comment);
    }

    public static function provideFromString(): array
    {
        return [
            ['09:00;09:30;"task \"1\"";"comment"', '09:00', '09:30', 'task "1"', 'comment'],
            ['09:00;     ;"task";"comment"', '09:00', '', 'task', 'comment'],
            ['09:00;     ;"";""', '09:00', '', '', ''],
        ];
    }

    public function testFromStringWithId(): void
    {
        $logEntry = LogEntry::fromString('09:00;     ;"";""', '2019-12-21');
        self::assertTrue($logEntry->isPersisted());
        self::assertSame('2019-12-21-09:00', $logEntry->getId());
    }

    #[DataProvider('provideDiff')]
    public function testDiff(string $start, string $stop, string $expected): void
    {
        $logEntry = LogEntryCreator::create($start, $stop);
        self::assertSame($expected, $logEntry->diff());
    }

    public static function provideDiff(): array
    {
        return [
            ['0:00', '0:00', '0:00'],
            ['0:00', '0:05', '0:05'],
            ['1:00', '0:00', '1:00'],
            ['23:59', '0:00', '23:59'],
            ['0:00', '23:59', '23:59'],
            ['0:00', '', ''],
        ];
    }

    #[DataProvider('provideGetDuration')]
    public function testGetDuration(
        string $startTime,
        string $stopTime,
        string|null $alternativeStopTime,
        string $expectedDuration,
        ?int $expectedDurationInMinutes
    ): void {
        $logEntry = new LogEntry(new Time($startTime));
        if ($stopTime) {
            $logEntry->stopTime = new Time($stopTime);
        }
        $alternativeStopTime = $alternativeStopTime ? new Time($alternativeStopTime) : null;
        $duration = $logEntry->getDuration($alternativeStopTime);
        $durationInMinutes = $logEntry->getDurationInMinutes($alternativeStopTime);
        self::assertSame($expectedDuration, $duration);
        self::assertSame($expectedDurationInMinutes, $durationInMinutes);
    }

    public static function provideGetDuration(): array
    {
        return [
            ['9:00', '10:00', null, '01:00', 60],
            ['9:00', '10:00', '11:00', '01:00', 60],
            ['9:00', '', '11:00', '02:00', 120],
            ['9:59', '', '10:02', '00:03', 3],
            ['9:59', '', '22:02', '12:03', 723],
            ['9:59', '', null, '', null],
        ];
    }

}
