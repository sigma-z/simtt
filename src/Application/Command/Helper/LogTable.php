<?php
declare(strict_types=1);

namespace Simtt\Application\Command\Helper;

use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;
use Simtt\Infrastructure\Service\Clock\Clock;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogTable
{

    /** @var Table */
    private $table;

    /** @var Time|null */
    private $lastStopTime;

    /** @var Clock */
    private $clock;

    public function __construct(Table $table, Clock $clock, ?Time $lastStopTime = null)
    {
        $this->table = $table;
        $this->table->setHeaders(['Start', 'Stop', 'Duration', 'Task', 'Comment']);
        $this->clock = $clock;
        $this->lastStopTime = $lastStopTime;
    }

    /**
     * @param LogEntry[] $logEntries
     */
    public function processLogEntries(array $logEntries): void
    {
        $rows = [];
        /** @var null|Time $stopTime */
        $stopTime = null;
        $date = null;
        foreach ($logEntries as $index => $entry) {
            if ($stopTime !== null && $stopTime->isOlderThan($entry->startTime)) {
                $rows[] = [
                    (string)$stopTime,
                    (string)$entry->startTime,
                    LogEntry::getTimeDuration($stopTime, $entry->startTime),
                    '-- no time logged --',
                    ''
                ];
            }

            $stopTime = $entry->stopTime;
            if (!$stopTime) {
                $stopTime = isset($logEntries[$index + 1]) ? $logEntries[$index + 1]->startTime : $this->lastStopTime;
            }

            if ($date !== $entry->getDate()) {
                if ($date !== null) {
                    $rows[] = new TableSeparator();
                }
                $date = $entry->getDate();
                $rows[] = [new TableCell('Date: ' .$date, ['colspan' => 5])];
                $rows[] = new TableSeparator();
            }
            $rows[] = [
                (string)$entry->startTime,
                $stopTime ? (string)$stopTime : '',
                $this->getDurationAsText($entry, $stopTime),
                $entry->task,
                $entry->comment,
            ];
        }

        $this->table->setRows($rows);
    }

    public function render(): void
    {
        $this->table->render();
    }

    private function getDurationAsText(LogEntry $entry, ?Time $stopTime): string
    {
        $isToday = $entry->getDate() === $this->clock->getDate();
        $isRunning = $stopTime === null;
        if (!$stopTime && $isToday) {
            $stopTime = Time::now($this->clock);
        }
        $duration = $entry->getDuration($stopTime);
        if ($isRunning) {
            $duration .= ($duration ? ' (running)' : 'running ...');
        }
        return $duration;
    }
}
