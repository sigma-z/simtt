<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   09.12.19
 */

namespace Test\Domain;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;
use Simtt\Domain\TimeTracker;
use Simtt\Infrastructure\Service\LogHandler;

class TimeTrackerTest extends TestCase
{

    /** @var LogEntry|null */
    private $currentLog;

    /** @var LogEntry|null */
    private $lastLog;

    public function testCommentThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $timeTracker = $this->createTimeTracker();
        $timeTracker->comment('This a comment');
    }

    public function testStartNewLogWithEmptyParams(): void
    {
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->start();
        self::assertInstanceOf(Time::class, $log->startTime);
        self::assertEmpty($log->task);
    }

    public function testStartNewLog(): void
    {
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->start(new Time('0000'), 'task name');
        self::assertSame(0, $log->startTime->getHour());
        self::assertSame(0, $log->startTime->getMinute());
        self::assertSame('task name', $log->task);
    }

    public function testStartRunningLogTask(): void
    {
        $this->setCurrentLog('200');
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->start(new Time('0220'), 'new task name');
        self::assertSame(2, $log->startTime->getHour());
        self::assertSame(20, $log->startTime->getMinute());
        self::assertSame('new task name', $log->task);
    }

    /**
     * Note: new task name will not be set, because the user has to decide interactively whether the new task name should be applied or not.
     */
    public function testStartRunningLogTaskWillNotBeOverwritten(): void
    {
        $this->setCurrentLog('200', 'old task name');
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->start(new Time('0220'), 'new task name');
        self::assertSame(2, $log->startTime->getHour());
        self::assertSame(20, $log->startTime->getMinute());
        self::assertSame('old task name', $log->task);
    }

    public function testStartBeforeLastLogThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->setLastLog('200', '300');
        $timeTracker = $this->createTimeTracker();
        $timeTracker->start(new Time('230'));
    }

    public function testStopOnEmptyLogThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $timeTracker = $this->createTimeTracker();
        $timeTracker->stop(new Time('945'));
    }

    public function testStopLastLog(): void
    {
        $taskName = 'Last log task name';
        $this->setLastLog('900', '930', $taskName);
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->stop(new Time('945'));
        self::assertSame('09:00', (string)$log->startTime);
        self::assertSame('09:45', (string)$log->stopTime);
        self::assertSame($taskName, $log->task);
    }

    public function testStopCurrentLog(): void
    {
        $taskName = 'Current log task name';
        $this->setCurrentLog('900', $taskName);
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->stop(new Time('945'));
        self::assertSame('09:00', (string)$log->startTime);
        self::assertSame('09:45', (string)$log->stopTime);
        self::assertSame($taskName, $log->task);
    }

    public function testStopBeforeStartThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->setCurrentLog('900');
        $timeTracker = $this->createTimeTracker();
        $timeTracker->stop(new Time('845'));
    }

    public function testCommentCurrentLog(): void
    {
        $this->setCurrentLog('900');
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->comment('Test comment');
        self::assertSame('Test comment', $log->comment);
    }

    public function testCommentLastLog(): void
    {
        $this->setLastLog('900', '9:45');
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->comment('Test comment');
        self::assertSame('Test comment', $log->comment);
    }

    public function testCommentCurrentLogOverLastLog(): void
    {
        $this->setLastLog('900', '9:45');
        $this->setCurrentLog('945');
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->comment('Test comment');
        self::assertSame('Test comment', $log->comment);
        self::assertSame($this->currentLog, $log);
        self::assertEmpty($this->lastLog->comment);
    }

    /**
     * @return TimeTracker
     */
    private function createTimeTracker(): TimeTracker
    {
        /** @var LogHandler|MockObject $logHandler */
        $logHandler = $this->createMock(LogHandler::class);
        $logHandler
            ->method('getCurrentLog')
            ->willReturn($this->currentLog);
        $logHandler
            ->method('getLastLog')
            ->willReturn($this->lastLog);

        return new TimeTracker($logHandler);
    }

    private function setCurrentLog(string $time, string $task = ''): void
    {
        $this->currentLog = new LogEntry();
        $this->currentLog->startTime = new Time($time);
        $this->currentLog->task = $task;
    }

    private function setLastLog(string $startTime, string $stopTime = null, string $taskName = ''): void
    {
        $this->lastLog = new LogEntry();
        $this->lastLog->startTime = new Time($startTime);
        $this->lastLog->stopTime = $stopTime ? new Time($stopTime) : null;
        $this->lastLog->task = $taskName;
    }
}
