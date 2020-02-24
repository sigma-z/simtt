<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Domain\LogHandlerInterface;
use Simtt\Domain\Model\LogEntry;
use Simtt\Infrastructure\Service\LogFile;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Tasks extends Command
{

    protected static $defaultName = 'tasks';

    /** @var LogHandlerInterface */
    private $logHandler;

    /** @var int */
    private $showTasksDefault;

    public function __construct(LogHandlerInterface $logHandler, int $showTasksDefault)
    {
        parent::__construct();

        $this->logHandler = $logHandler;
        $this->showTasksDefault = $showTasksDefault;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Lists recent logged tasks');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logFileFinder = $this->logHandler->getLogFileFinder();

        $logFiles = $logFileFinder->getLogFiles();
        if (count($logFiles) === 0) {
            $output->writeln('No entries found');
            return 0;
        }
        $tasks = $this->getTasks($logFiles);
        $table = new Table($output);
        $table->setHeaders(['#', 'Task', 'Count']);
        foreach ($tasks as $index => $taskData) {
            if ($index >= $this->showTasksDefault) {
                break;
            }
            $table->addRow($this->getTableRow($index + 1, $taskData['task'], $taskData['count']));
        }
        $table->render();
        return 0;
    }

    /**
     * @param LogFile[] $logFiles
     * @return array[]
     */
    private function getTasks(array $logFiles): array
    {
        $tasks = [];
        foreach ($logFiles as $logFile) {
            $logEntries = $logFile->getEntries();
            foreach ($logEntries as $logEntry) {
                $tasks = $this->getTaskData($tasks, $logEntry);
            }
            if (count($tasks) >= $this->showTasksDefault) {
                break;
            }
        }
        usort($tasks, [$this, 'sort']);
        return $tasks;
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    private function sort(array $a, array $b): int
    {
        $cmp = $b['count'] <=> $a['count'];
        if ($cmp === 0) {
            return strcasecmp($a['task'], $b['task']);
        }
        return $cmp;
    }

    /**
     * @param array[]  $tasks
     * @param LogEntry $logEntry
     * @return array[]
     */
    private function getTaskData(array $tasks, LogEntry $logEntry): array
    {
        if (!$logEntry->task) {
            return $tasks;
        }

        /** @var string $taskKey */
        $taskKey = mb_strtolower($logEntry->task);
        if (!isset($tasks[$taskKey])) {
            $tasks[$taskKey] = ['task' => $logEntry->task, 'count' => 0];
        }
        $tasks[$taskKey]['count']++;
        return $tasks;
    }

    private function getTableRow(int $index, string $task, int $count): array
    {
        return ["#$index", $task, $count];
    }
}
