<?php
declare(strict_types=1);

namespace Test\Helper;

use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;
use Simtt\Infrastructure\Service\Clock\Clock;
use Simtt\Infrastructure\Service\LogFile;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogEntryCreator
{

    public static function create(string $startTime, string $stopTime = '', string $taskName = '', string $comment = ''): LogEntry
    {
        $logEntry = new LogEntry(new Time($startTime), $taskName);
        $logEntry->comment = $comment;
        if ($stopTime) {
            $logEntry->stopTime = new Time($stopTime);
        }
        return $logEntry;
    }

    public static function createWithId(string $startTime, string $stopTime, string $taskName, string $comment, Clock $clock): LogEntry
    {
        $logEntry = self::create($startTime, $stopTime, $taskName, $comment);
        $logEntry->setId($clock->getDate() . '-' . $startTime);
        return $logEntry;
    }

    public static function createToString(string $startTime, string $stopTime = '', string $taskName = '', string $comment = ''): string
    {
        return (string)self::create($startTime, $stopTime, $taskName, $comment);
    }

    public static function setUpLogFile(string $date, array $entries): void
    {
        [$year, $month, ] = explode('-', $date);
        $dir = LOG_DIR . "/$year/$month";
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents("$dir/$date" . LogFile::FILE_EXT, implode("\n", $entries) . "\n");
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
