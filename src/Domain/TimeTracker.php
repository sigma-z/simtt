<?php
declare(strict_types=1);

namespace Simtt\Domain;

use Simtt\Domain\Exception\NoLogEntryFoundException;
use Simtt\Domain\Exception\StartTimeBeforeLastLogEntryException;
use Simtt\Domain\Exception\StopTimeBeforeStartException;
use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;
use Simtt\Infrastructure\Service\LogHandler;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class TimeTracker
{

    /** @var LogHandler */
    private $logHandler;

    public function __construct(LogHandler $logHandler)
    {
        $this->logHandler = $logHandler;
    }

    public function start(Time $startTime = null, string $taskName = ''): LogEntry
    {
        $lastLog = $this->logHandler->getLastLog();
        $startTime = $startTime ?: Time::now();
        if ($lastLog && $lastLog->stopTime->isOlderThan($startTime)) {
            throw self::startTimeBeforeLastLogEntryException();
        }

        $log = $this->logHandler->getCurrentLog() ?: new LogEntry();
        $log->startTime = $startTime;
        if (!$log->task && $taskName) {
            $log->task = $taskName;
        }
        return $log;
    }

    public function stop(Time $stopTime = null, string $taskName = ''): LogEntry
    {
        $log = $this->getCorrespondingLogEntry();
        $stopTime = $stopTime ?: Time::now();
        if ($log->startTime->isOlderThan($stopTime)) {
            throw self::stopTimeBeforeStartException();
        }
        $log->stopTime = $stopTime;
        if (!$log->task && $taskName) {
            $log->task = $taskName;
        }
        return $log;
    }

    public function task(string $task): LogEntry
    {
        $log = $this->getCorrespondingLogEntry();
        $log->task = $task;
        return $log;
    }

    public function comment(string $comment): LogEntry
    {
        $log = $this->getCorrespondingLogEntry();
        $log->comment = $comment;
        return $log;
    }

    private function getCorrespondingLogEntry(): LogEntry
    {
        $log = $this->logHandler->getCurrentLog() ?: $this->logHandler->getLastLog();
        if (!$log) {
            throw self::noLogEntryFoundException();
        }
        return $log;
    }

    private static function startTimeBeforeLastLogEntryException(): StartTimeBeforeLastLogEntryException
    {
        return new StartTimeBeforeLastLogEntryException('Stop time of last log is older than start time!');
    }

    private static function stopTimeBeforeStartException(): StopTimeBeforeStartException
    {
        throw new StopTimeBeforeStartException('Stop time cannot be before start time!');
    }

    private static function noLogEntryFoundException(): NoLogEntryFoundException
    {
        return new NoLogEntryFoundException('No log entry found!');
    }

}
