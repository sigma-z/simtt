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
        /** @var null|Time $stopTime */
        $stopTime = null;
        foreach ($logEntries as $index => $entry) {
            if ($stopTime !== null && $stopTime->isOlderThan($entry->startTime)) {
                $id = '-- no time logged --';
                $row = $rows[$id] ?? self::getEmptyRow();
                $timeDurationInMinutes = LogEntry::getTimeDurationInMinutes($stopTime, $entry->startTime);
                $row[self::DURATION] = isset($row[self::DURATION]) ? $row[self::DURATION] + $timeDurationInMinutes : $timeDurationInMinutes;
                $row[self::COUNT]++;
                $row[self::TASK] = $id;
                $row[self::COMMENT] = '';
                $rows[$id] = $row;
            }

            $stopTime = $entry->stopTime;
            if (!$stopTime) {
                $stopTime = isset($logEntries[$index + 1]) ? $logEntries[$index + 1]->startTime : null;
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

}
