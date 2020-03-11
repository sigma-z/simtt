<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service;

use Simtt\Application\Task\RecentTasksPrinterInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class RecentTasksPrinter implements RecentTasksPrinterInterface
{

    /** @var RecentTaskList */
    private $recentTaskList;

    /** @var int */
    private $showTasksDefault;

    public function __construct(RecentTaskList $recentTaskList, int $showTaskDefault)
    {
        $this->recentTaskList = $recentTaskList;
        $this->showTasksDefault = $showTaskDefault;
    }

    /**
     * @param OutputInterface $output
     * @return string[]
     */
    public function outputTasks(OutputInterface $output): array
    {
        $tasks = $this->recentTaskList->getTasks($this->showTasksDefault);
        $length = strlen((string)count($tasks));
        foreach ($tasks as $index => $task) {
            $pos = (string)($index + 1);
            $indexFormatted = "#$pos" . str_repeat(' ', $length - strlen($pos));
            $output->writeln($indexFormatted . ' ' . $task->getTask());
        }
        return $tasks;
    }
}
