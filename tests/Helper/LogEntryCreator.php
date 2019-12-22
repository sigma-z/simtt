<?php
declare(strict_types=1);

namespace Test\Helper;

use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogEntryCreator
{

    public static function create(string $startTime, string $stopTime, string $taskName = '', string $comment = ''): LogEntry
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
