<?php
declare(strict_types=1);

namespace Simtt\Application\Command\Helper;

use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;
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

    public function __construct(Table $table)
    {
        $this->table = $table;
        $this->table->setHeaders(['Duration', 'Count', 'Task', 'Comment']);
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

        foreach ($logEntries as $index => $entry) {
            if ($stopTime !== null && $stopTime->isOlderThan($entry->startTime)) {
                $id = '-- no time logged --';
                $row = $rows[$id] ?? self::getEmptyRow();
                $timeDurationInMinutes = LogEntry::getTimeDurationInMinutes($stopTime, $entry->startTime);
                $duration = isset($row[self::DURATION]) ? $row[self::DURATION] + $timeDurationInMinutes : $timeDurationInMinutes;
                $row[self::DURATION] = $duration;
                $row[self::COUNT]++;
                $row[self::TASK] = $id;
                $row[self::COMMENT] = '';
                $rows[$id] = $row;
            }

            $stopTime = $entry->stopTime;
            $stopDate = $entry->getDate();
            if (!$stopTime) {
                $stopTime = isset($logEntries[$index + 1]) ? $logEntries[$index + 1]->startTime : null;
            }

            if ($startTime === null) {
                $startDate = $entry->getDate();
                $startTime = $entry->startTime;
            }

            $duration = $entry->getDurationInMinutes($stopTime);
            $totalDuration += $duration;
            $id = strtolower($entry->task);
            $row = $rows[$id] ?? self::getEmptyRow();
            $row[self::DURATION] = $duration === null ? null : $row[self::DURATION] + $duration;
            $row[self::COUNT]++;
            $row[self::TASK] = $entry->task;
            $row[self::COMMENT] = $row[self::COMMENT] ? $row[self::COMMENT] . "\n" . $entry->comment : $entry->comment;
            $rows[$id] = $row;
        }

        foreach ($rows as $index => $row) {
            if (isset($row[self::DURATION])) {
                $rows[$index][self::DURATION] = self::getDurationAsString($row[self::DURATION]);
            }
        }

        uasort($rows, [$this, 'sort']);

        $lastKey = array_key_last($rows);
        if (!isset($rows[$lastKey][self::DURATION])) {
            $rows[$lastKey][self::DURATION] = 'running ...';
        }

        $totalRow = [
            self::getDurationAsString($totalDuration),
            count($logEntries),
            'Total time',
            self::getLoggedRange($startDate, $startTime, $stopDate, $stopTime)
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
     * @param Time|null   $startTime
     * @param string|null $stopDate
     * @param Time|null   $stopTime
     * @return string
     */
    private static function getLoggedRange(?string $startDate, ?Time $startTime, ?string $stopDate, ?Time $stopTime): string
    {
        if ($startDate === $stopDate) {
            $start = $startTime;
            $stop = $stopTime;
        }
        else {
            $start = "$startDate $startTime";
            $stop = "$stopDate $stopTime";
        }

        return "Logged from $start to " . ($stop ?: '?');
    }
}
