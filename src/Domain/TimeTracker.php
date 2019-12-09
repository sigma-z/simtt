<?php
declare(strict_types=1);

namespace Simtt\Domain;

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
        $log = $this->logHandler->getCurrentLog();
        if (!$log) {
            $log = new LogEntry();
        }
        $log->startTime = $startTime ?: Time::now();
        if (!$log->task && $taskName) {
            $log->task = $taskName;
        }
        return $log;
    }

    public function stop(Time $stopTime = null, string $taskName = ''): LogEntry
    {

    }

    public function comment(string $comment): LogEntry
    {
        $log = $this->logHandler->getCurrentLog() ?? $this->logHandler->getLastLog();
        if (!$log) {
            throw new \RuntimeException('No log entry found for commenting!');
        }
        $log->comment = $comment;
        return $log;
    }
}
