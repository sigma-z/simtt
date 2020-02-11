<?php
declare(strict_types=1);

namespace Simtt\Domain;

use Simtt\Domain\Exception\InvalidLogEntryException;
use Simtt\Domain\Exception\NoLogEntryFoundException;
use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class TimeTracker
{

    /** @var LogHandlerInterface */
    private $logHandler;

    /** @var int */
    private $precision;

    public function __construct(LogHandlerInterface $logHandler, int $precision = 1)
    {
        $this->logHandler = $logHandler;
        $this->precision = $precision;
    }

    public function getLogHandler(): LogHandlerInterface
    {
        return $this->logHandler;
    }

    public function updateStart(Time $startTime = null, string $taskName = '', string $comment = ''): LogEntry
    {
        $lastLog = $this->logHandler->getLastLog();
        $startTime = $startTime ?: Time::now();
        $startTime->roundBy($this->precision);
        if ($lastLog && $lastLog->stopTime && $lastLog->stopTime->isOlderThan($startTime)) {
            throw new InvalidLogEntryException('Stop time of last log is older than start time!');
        }
        $logBefore = $this->logHandler->getLogReverseIndex(1);
        if ($logBefore) {
            if ($logBefore->stopTime && $logBefore->stopTime->isNewerThan($startTime)) {
                throw new InvalidLogEntryException('Stop time of last log is older than start time!');
            }
            if ($logBefore->startTime->isNewerThan($startTime)) {
                throw new InvalidLogEntryException('Start time of last log is older than start time!');
            }
        }

        if (!$lastLog) {
            $lastLog = new LogEntry($startTime);
        }

        $lastLog->startTime = $startTime;
        if ($taskName !== '') {
            $lastLog->task = $taskName;
        }
        if ($comment !== '') {
            $lastLog->comment = $comment;
        }

        return $lastLog;
    }

    public function start(Time $startTime = null, string $taskName = '', string $comment = ''): LogEntry
    {
        $lastLog = $this->logHandler->getLastLog();
        $startTime = $startTime ?: Time::now();
        $startTime->roundBy($this->precision);
        if ($lastLog && $lastLog->stopTime && $lastLog->stopTime->isNewerThan($startTime)) {
            throw new InvalidLogEntryException('Stop time of last log is newer than the new start time!');
        }
        if ($lastLog && $lastLog->startTime->isNewerThan($startTime)) {
            throw new InvalidLogEntryException('Start time of last log is newer than the new start time!');
        }
        return new LogEntry($startTime, $taskName, $comment);
    }

    public function updateStop(Time $stopTime = null, string $taskName = '', string $comment = ''): LogEntry
    {
        $log = $this->getLogEntryOrThrowNotFoundException();
        return $this->stopTimer($log, $stopTime, $taskName, $comment);
    }

    public function stop(Time $stopTime = null, string $taskName = '', string $comment = ''): LogEntry
    {
        $log = $this->getLogEntryOrThrowNotFoundException();
        if ($log->stopTime) {
            throw new InvalidLogEntryException("Cannot stop a stopped timer, please use update stop 'stop*'");
        }
        return $this->stopTimer($log, $stopTime, $taskName, $comment);
    }

    private function getLogEntryOrThrowNotFoundException(): LogEntry
    {
        $log = $this->logHandler->getLastLog();
        if (!$log) {
            throw self::noLogEntryFoundException();
        }
        return $log;
    }

    private static function noLogEntryFoundException(): NoLogEntryFoundException
    {
        return new NoLogEntryFoundException('No log entry found!');
    }

    private function stopTimer(LogEntry $log, ?Time $stopTime, string $taskName, string $comment): LogEntry
    {
        $stopTime = $stopTime ?: Time::now();
        $stopTime->roundBy($this->precision);
        if ($log->startTime->isNewerThan($stopTime)) {
            throw new InvalidLogEntryException('Stop time cannot be before start time!');
        }

        $log->stopTime = $stopTime;
        if ($taskName !== '') {
            $log->task = $taskName;
        }
        if ($comment !== '') {
            $log->comment = $comment;
        }
        return $log;
    }
}
