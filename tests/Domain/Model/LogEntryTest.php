<?php
declare(strict_types=1);

namespace Test\Domain\Model;

use PHPUnit\Framework\TestCase;
use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;

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
        $logEntry = self::createLogEntry($startTime, $stopTime, $taskName, $comment);
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

    private static function createLogEntry(string $startTime, string $stopTime, string $taskName, string $comment): LogEntry
    {
        $logEntry = new LogEntry();
        $logEntry->task = $taskName;
        $logEntry->comment = $comment;
        if ($startTime) {
            $logEntry->startTime = new Time($startTime);
        }
        if ($stopTime) {
            $logEntry->stopTime = new Time($stopTime);
        }
        return $logEntry;
    }

}
