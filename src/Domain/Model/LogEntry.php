<?php
declare(strict_types=1);

namespace Simtt\Domain\Model;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogEntry
{

    /** @var Time|null */
    public $startTime;

    /** @var Time|null */
    public $stopTime;

    /** @var string */
    public $task = '';

    /** @var string */
    public $comment = '';

    /**
     * @param Time|null $startTime
     * @param string    $task
     * @return LogEntry
     */
    public static function create(?Time $startTime, string $task): LogEntry
    {
        $log = new self();
        $log->startTime = $startTime;
        $log->task = $task;
        return $log;
    }

}
