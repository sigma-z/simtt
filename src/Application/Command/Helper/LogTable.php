<?php
declare(strict_types=1);

namespace Simtt\Application\Command\Helper;

use Simtt\Domain\Model\Time;
use Symfony\Component\Console\Helper\Table;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogTable
{

    /** @var Table */
    private $table;

    /** @var Time|null */
    private $lastStopTime;

    /** @var bool */
    private $reverseOrder;

    public function __construct(Table $table, ?Time $lastStopTime = null, bool $reverseOrder = false)
    {
        $this->table = $table;
        $this->table->setHeaders(['Start', 'Stop', 'Duration', 'Task', 'Comment']);
        $this->lastStopTime = $lastStopTime;
        $this->reverseOrder = $reverseOrder;
    }

    public function processLogEntries(array $logEntries): void
    {
        $rows = [];
        foreach ($logEntries as $index => $entry) {
            $stopTime = isset($logEntries[$index + 1]) ? $logEntries[$index + 1]->startTime : $this->lastStopTime;
            $rows[] = [
                (string)$entry->startTime,
                $stopTime ? (string)$stopTime : '',
                $entry->getDuration($stopTime) ?: 'running ...',
                $entry->task,
                $entry->comment,
            ];
        }
        if ($this->reverseOrder) {
            $rows = array_reverse($rows);
        }
        $this->table->setRows($rows);
    }

    public function render(): void
    {
        $this->table->render();
    }

}
