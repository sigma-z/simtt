<?php
declare(strict_types=1);

namespace Test\Domain\Model;

use PHPUnit\Framework\TestCase;
use Simtt\Domain\Model\LogEntry;
use Test\Helper\LogEntryCreator;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogEntryTest extends TestCase
{

    /**
     * @dataProvider provideToString
     * @param string $startTime
     * @param string $stopTime
     * @param string $taskName
     * @param string $comment
     * @param string $expected
     */
    public function testToString(string $startTime, string $stopTime, string $taskName, string $comment, string $expected): void
    {
        $logEntry = LogEntryCreator::create($startTime, $stopTime, $taskName, $comment);
        self::assertEquals($expected, (string)$logEntry);
    }

    public function provideToString(): array
    {
        return [
            ['', '', '', '', ''],
            ['', '900', 'task', 'comment', ''],
            ['900', '', 'task', 'comment', '09:00;     ;"task";"comment"'],
            ['900', '1000', 'task', 'comment', '09:00;10:00;"task";"comment"'],
            ['900', '1000', '', '', '09:00;10:00;"";""'],
            ['900', '1000', '', 'comment', '09:00;10:00;"";"comment"'],
            ['900', '1000', 'task', '', '09:00;10:00;"task";""'],
        ];
    }

    /**
     * @dataProvider provideFromString
     * @param string $raw
     * @param string $expectedStart
     * @param string $expectedStop
     * @param string $expectedTask
     * @param string $expectedComment
     */
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

    public function provideFromString(): array
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
}
