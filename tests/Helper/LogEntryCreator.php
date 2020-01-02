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

    public static function create(string $startTime, string $stopTime = '', string $taskName = '', string $comment = ''): LogEntry
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

    public static function createToString(string $startTime, string $stopTime = '', string $taskName = '', string $comment = ''): string
    {
        return (string)self::create($startTime, $stopTime, $taskName, $comment);
    }

    public static function setUpLogFile(string $date, array $entries): void
    {
        [$year, $month, ] = explode('-', $date);
        $dir = VirtualFileSystem::LOG_DIR . "/$year/$month";
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents("$dir/$date.log", implode("\n", $entries) . "\n");
    }

    public static function setUpLogFileToday(array $entries): void
    {
        $date = (new \DateTime())->format('Y-m-d');
        self::setUpLogFile($date, $entries);
    }

    public static function setUpLogFileYesterday(array $entries): void
    {
        $yesterday = (new \DateTime())->sub(new \DateInterval('P1D'))->format('Y-m-d');
        self::setUpLogFile($yesterday, $entries);
    }

}
