<?php
declare(strict_types=1);

namespace Simtt\Application\Command\Helper;

use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;
use Simtt\Infrastructure\Service\Clock\Clock;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogAggregationTable
{

    private const DURATION = 0;
    private const COUNT = 1;
    private const TASK = 2;
    private const COMMENT = 3;

    /** @var Table */
    private $table;

    /** @var Clock */
    private $clock;

    public function __construct(Table $table, Clock $clock)
    {
        $this->table = $table;
        $this->table->setHeaders(['Duration', 'Count', 'Task', 'Comment']);
        $this->clock = $clock;
    }

    private static function getEmptyRow(): array
    {
        return [
            self::DURATION => 0,
            self::COUNT => 0,
            self::TASK => '',
            self::COMMENT => ''
        ];
    }

    /**
     * @param LogEntry[] $logEntries
     */
    public function processLogEntries(array $logEntries): void
    {
        $rows = [];
        $totalDuration = 0;
        $startDate = null;
        $startTime = null;
        /** @var null|Time $stopTime */
        $stopTime = null;
        $stopDate = null;
        $taskRunningHash = null;

        foreach ($logEntries as $index => $entry) {
            if ($stopTime !== null && $stopTime->isOlderThan($entry->startTime)) {
                $hash = '-- no time logged --';
                $row = $rows[$hash] ?? self::getEmptyRow();
                $timeDurationInMinutes = LogEntry::getTimeDurationInMinutes($stopTime, $entry->startTime);
                $duration = isset($row[self::DURATION]) ? $row[self::DURATION] + $timeDurationInMinutes : $timeDurationInMinutes;
                $row[self::DURATION] = $duration;
                $row[self::COUNT]++;
                $row[self::TASK] = $hash;
                $row[self::COMMENT] = '';
                $rows[$hash] = $row;
            }

            $stopTime = $entry->stopTime;
            $stopDate = $entry->getDate();
            $hash = strtolower($entry->task);
            if (!$stopTime) {
                $nextLogEntry = $logEntries[$index + 1] ?? null;
                if ($nextLogEntry) {
                    $stopTime = $nextLogEntry->startTime;
                }
                else {
                    $stopTime = $this->getCurrentTimeAsStopTime($entry);
                    $taskRunningHash = $hash;
                }
            }

            if ($startTime === null) {
                $startDate = $entry->getDate();
                $startTime = $entry->startTime;
            }

            $duration = $entry->getDurationInMinutes($stopTime);
            $totalDuration += $duration;
            $row = $rows[$hash] ?? self::getEmptyRow();
            $row[self::DURATION] += $duration;
            $row[self::COUNT]++;
            $row[self::TASK] = $entry->task;
            $row[self::COMMENT] = $row[self::COMMENT] ? $row[self::COMMENT] . "\n" . $entry->comment : $entry->comment;
            $rows[$hash] = $row;
        }

        foreach ($rows as $index => $row) {
            if (isset($row[self::DURATION])) {
                $rows[$index][self::DURATION] = self::getDurationAsString($row[self::DURATION]);
            }
        }

        uasort($rows, [$this, 'sort']);

        $rows = self::appendTaskRunningInfoToDurationToRows($rows, $taskRunningHash);

        $isRunning = $taskRunningHash !== null;

        $totalRow = [
            self::getTotalDurationAsString($totalDuration, $isRunning),
            count($logEntries),
            'Total time',
            self::getLoggedRange((string)$startDate, (string)$startTime, (string)$stopDate, (string)$stopTime, $isRunning)
        ];

        $rows[] = new TableSeparator();
        $rows[] = $totalRow;
        $this->table->setRows($rows);
    }

    private static function getDurationAsString(int $duration): string
    {
        $hours = floor($duration / 60);
        $minutes = $duration - ($hours * 60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    private static function getTotalDurationAsString(?int $totalDuration, bool $isRunning): string
    {
        $duration = self::getDurationAsString($totalDuration);
        if ($isRunning) {
            $duration = self::appendTaskRunningInfoToDuration($duration);
        }
        return $duration;
    }

    private static function appendTaskRunningInfoToDuration(?string $duration): string
    {
        return $duration ? $duration . ' (running)' : 'running ...';
    }

    private static function appendTaskRunningInfoToDurationToRows(array $rows, ?string $taskRunningHash): array
    {
        if ($taskRunningHash !== null) {
            $rows[$taskRunningHash][self::DURATION] = self::appendTaskRunningInfoToDuration($rows[$taskRunningHash][self::DURATION]);
        }
        return $rows;
    }

    public function render(): void
    {
        $this->table->render();
    }

    private function sort($a, $b): int
    {
        return $b[self::DURATION] <=> $a[self::DURATION];
    }

    /**
     * @param string|null $startDate
     * @param string      $startTime
     * @param string|null $stopDate
     * @param string      $stopTime
     * @param bool        $isRunning
     * @return string
     */
    private static function getLoggedRange(
        string $startDate,
        string $startTime,
        string $stopDate,
        string $stopTime,
        bool $isRunning
    ): string {
        if ($startDate === $stopDate) {
            $start = $startTime;
            $stop = $stopTime;
        }
        else {
            $start = "$startDate $startTime";
            $stop = "$stopDate $stopTime";
        }

        $range = "Logged from $start to " . ($stop ?: '?');
        if ($stop && $isRunning) {
            $range .= ' (running)';
        }
        return $range;
    }

    private function getCurrentTimeAsStopTime(LogEntry $entry): ?Time
    {
        $isToday = $entry->getDate() === $this->clock->getDate();
        return $isToday ? new Time($this->clock->getTime()) : null;
    }
}
