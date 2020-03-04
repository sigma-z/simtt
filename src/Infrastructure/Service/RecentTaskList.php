<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service;

use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\LogFileInterface;
use Simtt\Domain\Model\RecentTask;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class RecentTaskList
{

    /** @var LogFileFinder */
    private $logFileFinder;

    /** @var int */
    private $showTasksDefault;

    public function __construct(LogFileFinder $logFileFinder, int $showTasksDefault)
    {
        $this->logFileFinder = $logFileFinder;
        $this->showTasksDefault = $showTasksDefault;
    }

    /**
     * @return RecentTask[]
     */
    public function getTasks(): array
    {
        $logFiles = $this->logFileFinder->getLogFiles();
        $logFiles = array_reverse($logFiles);
        $tasks = $this->readTasks($logFiles);
        usort($tasks, [$this, 'sort']);
        return array_values($tasks);
    }

    /**
     * @param LogFileInterface[] $logFiles
     * @return RecentTask[]
     */
    private function readTasks(array $logFiles): array
    {
        $tasks = [];
        foreach ($logFiles as $logFile) {
            $logEntries = $logFile->getEntries();
            foreach ($logEntries as $logEntry) {
                $tasks = $this->getTaskData($tasks, $logEntry);
            }
            if (count($tasks) >= $this->showTasksDefault) {
                return array_slice($tasks, 0, $this->showTasksDefault);
            }
        }
        return $tasks;
    }

    private function sort(RecentTask $a, RecentTask $b): int
    {
        $cmp = $b->getCount() <=> $a->getCount();
        if ($cmp === 0) {
            return strcasecmp($a->getTask(), $b->getTask());
        }
        return $cmp;
    }

    /**
     * @param RecentTask[]  $tasks
     * @param LogEntry $logEntry
     * @return RecentTask[]
     */
    private function getTaskData(array $tasks, LogEntry $logEntry): array
    {
        if (!$logEntry->task) {
            return $tasks;
        }

        /** @var string $taskKey */
        $taskKey = mb_strtolower($logEntry->task);
        if (isset($tasks[$taskKey])) {
            $tasks[$taskKey]->increment();
        }
        else {
            $tasks[$taskKey] = new RecentTask($logEntry->task);
        }
        return $tasks;
    }

}
