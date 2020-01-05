<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   09.12.19
 */

namespace Test\Domain;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Simtt\Domain\Exception\NoLogEntryFoundException;
use Simtt\Domain\Exception\InvalidLogEntryException;
use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;
use Simtt\Domain\TimeTracker;
use Simtt\Infrastructure\Service\LogHandler;
use Test\Helper\LogEntryCreator;

class TimeTrackerTest extends TestCase
{

    /** @var LogEntry|null */
    private $beforeLastLog;

    /** @var LogEntry|null */
    private $lastLog;


    public function testStartNewLogWithEmptyParams(): void
    {
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->start();
        self::assertInstanceOf(Time::class, $log->startTime);
        self::assertEmpty($log->task);
    }

    public function testUpdateStartNewLogWithEmptyParams(): void
    {
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->updateStart();
        self::assertInstanceOf(Time::class, $log->startTime);
        self::assertEmpty($log->task);
    }

    public function testStartNewLog(): void
    {
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->updateStart(new Time('0000'), 'task name');
        self::assertSame('00:00', (string)$log->startTime);
        self::assertSame('task name', $log->task);
    }

    public function testUpdateStartNewLog(): void
    {
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->start(new Time('0000'), 'task name');
        self::assertSame('00:00', (string)$log->startTime);
        self::assertSame('task name', $log->task);
    }

    public function testStartRunningLogTask(): void
    {
        $this->setLastLog('200');
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->start(new Time('0220'), 'new task name');
        self::assertSame('02:20', (string)$log->startTime);
        self::assertSame('new task name', $log->task);
        self::assertNotSame($log, $this->lastLog);
    }

    public function testUpdateStartRunningLogTask(): void
    {
        $this->setLastLog('200');
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->updateStart(new Time('0220'), 'new task name');
        self::assertSame('02:20', (string)$log->startTime);
        self::assertSame('new task name', $log->task);
        self::assertSame($log, $this->lastLog);
    }

    /**
     * Note: new task name will not be set, because the user has to decide interactively whether the new task name should be applied or not.
     */
    public function testUpdateStartRunningLogTaskWillNotBeOverwritten(): void
    {
        $this->setLastLog('200', 'old task name');
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->updateStart(new Time('0220'), 'new task name');
        self::assertSame('02:20', (string)$log->startTime);
        self::assertSame('old task name', $log->task);
        self::assertSame($log, $this->lastLog);
    }

    public function testUpdateStartForStoppedLog(): void
    {
        $this->setLastLog('200', 'task', '300');
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->updateStart(new Time('230'));
        self::assertSame('02:30', (string)$log->startTime);
        self::assertSame('03:00', (string)$log->stopTime);
        self::assertSame($log, $this->lastLog);
    }

    public function testStartBeforeLastLogStopThrowsException(): void
    {
        $this->expectException(InvalidLogEntryException::class);
        $this->setLastLog('200', 'task', '300');
        $timeTracker = $this->createTimeTracker();
        $timeTracker->start(new Time('230'));
    }

    public function testStartBeforeLastLogStartThrowsException(): void
    {
        $this->expectException(InvalidLogEntryException::class);
        $this->setLastLog('200', 'task');
        $timeTracker = $this->createTimeTracker();
        $timeTracker->start(new Time('130'));
    }

    public function testStopOnStoppedEntryThrowsException(): void
    {
        $this->expectException(InvalidLogEntryException::class);
        $this->setLastLog('200', 'task', '300');
        $timeTracker = $this->createTimeTracker();
        $timeTracker->stop(new Time('330'));
    }

    public function testUpdateStartBeforeCurrentLogStopThrowsException(): void
    {
        $this->expectException(InvalidLogEntryException::class);
        $this->setLastLog('200', 'task', '300');
        $timeTracker = $this->createTimeTracker();
        $timeTracker->updateStart(new Time('330'));
    }

    public function testUpdateStartBeforeLastLogStartThrowsException(): void
    {
        $this->expectException(InvalidLogEntryException::class);
        $this->setBeforeLastLog('200');
        $this->setLastLog('300');
        $timeTracker = $this->createTimeTracker();
        $timeTracker->updateStart(new Time('130'));
    }

    public function testUpdateStartBeforeLastLogStopThrowsException(): void
    {
        $this->expectException(InvalidLogEntryException::class);
        $this->setBeforeLastLog('200', 'task', '300');
        $this->setLastLog('300');
        $timeTracker = $this->createTimeTracker();
        $timeTracker->updateStart(new Time('230'));
    }

    public function testUpdateStopOnStoppedEntry(): void
    {
        $this->setLastLog('200', 'task', '300');
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->updateStop(new Time('230'));
        self::assertSame($this->lastLog, $log);
        self::assertSame('02:00', (string)$log->startTime);
        self::assertSame('02:30', (string)$log->stopTime);
    }

    public function testStopOnEmptyLogThrowsException(): void
    {
        $this->expectException(NoLogEntryFoundException::class);
        $timeTracker = $this->createTimeTracker();
        $timeTracker->stop(new Time('945'));
    }

    public function testUpdateStopOnEmptyLogThrowsException(): void
    {
        $this->expectException(NoLogEntryFoundException::class);
        $timeTracker = $this->createTimeTracker();
        $timeTracker->updateStop(new Time('945'));
    }

    public function testUpdateStopLastLog(): void
    {
        $taskName = 'Last log task name';
        $this->setLastLog('900', $taskName, '930');
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->updateStop(new Time('945'));
        self::assertSame('09:00', (string)$log->startTime);
        self::assertSame('09:45', (string)$log->stopTime);
        self::assertSame($taskName, $log->task);
    }

    public function testStopLastLog(): void
    {
        $taskName = 'Last log task name';
        $this->setLastLog('900', $taskName);
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->stop(new Time('945'));
        self::assertSame('09:00', (string)$log->startTime);
        self::assertSame('09:45', (string)$log->stopTime);
        self::assertSame($taskName, $log->task);
    }

    public function testStopBeforeStartThrowsException(): void
    {
        $this->expectException(InvalidLogEntryException::class);
        $this->setLastLog('900');
        $timeTracker = $this->createTimeTracker();
        $timeTracker->stop(new Time('845'));
    }

    public function testUpdateStopBeforeStartThrowsException(): void
    {
        $this->expectException(InvalidLogEntryException::class);
        $this->setLastLog('900');
        $timeTracker = $this->createTimeTracker();
        $timeTracker->updateStop(new Time('845'));
    }

    public function testTaskThrowsException(): void
    {
        $this->expectException(NoLogEntryFoundException::class);
        $timeTracker = $this->createTimeTracker();
        $timeTracker->task('task');
    }

    public function testTaskLastLog(): void
    {
        $this->setLastLog('900', '9:45');
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->task('Test task');
        self::assertSame('Test task', $log->task);
        self::assertSame($this->lastLog, $log);
    }

    public function testCommentThrowsException(): void
    {
        $this->expectException(NoLogEntryFoundException::class);
        $timeTracker = $this->createTimeTracker();
        $timeTracker->comment('This a comment');
    }

    public function testCommentLastLog(): void
    {
        $this->setLastLog('900', '9:45');
        $timeTracker = $this->createTimeTracker();
        $log = $timeTracker->comment('Test comment');
        self::assertSame('Test comment', $log->comment);
    }

    /**
     * @return TimeTracker
     */
    private function createTimeTracker(): TimeTracker
    {
        /** @var LogHandler|MockObject $logHandler */
        $logHandler = $this->createMock(LogHandler::class);
        $logHandler
            ->method('getLastLog')
            ->willReturn($this->lastLog);
        $logHandler
            ->method('getLogReverseIndex')
            ->willReturn($this->beforeLastLog);

        return new TimeTracker($logHandler);
    }

    private function setLastLog(string $time, string $task = '', string $stopTime = ''): void
    {
        $this->lastLog = LogEntryCreator::create($time, $stopTime, $task);
    }

    private function setBeforeLastLog(string $time, string $task = '', string $stopTime = ''): void
    {
        $this->beforeLastLog = LogEntryCreator::create($time, $stopTime, $task);
    }
}
