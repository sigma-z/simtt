<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Application\Task\RecentTaskListInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Tasks extends Command
{

    protected static $defaultName = 'tasks';

    /** @var RecentTaskListInterface */
    private $recentTaskList;

    /** @var int */
    private $showTasksDefault;

    public function __construct(RecentTaskListInterface $recentTaskList, int $showTasksDefault)
    {
        parent::__construct();

        $this->recentTaskList = $recentTaskList;
        $this->showTasksDefault = $showTasksDefault;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Lists recent logged tasks');
        $this->addArgument('num', InputArgument::OPTIONAL, 'number of entries to show', $this->showTasksDefault);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $num = (int)$input->getArgument('num') ?: $this->showTasksDefault;
        $tasks = $this->recentTaskList->getTasks($num);
        if (count($tasks) === 0) {
            $output->writeln('No entries found');
            return 0;
        }

        $table = new Table($output);
        $table->setHeaders(['#', 'Task', 'Count']);
        foreach ($tasks as $index => $recentTask) {
            if ($index >= $num) {
                break;
            }
            $tableRow = $this->getTableRow($index + 1, $recentTask->getTask(), $recentTask->getCount());
            $table->addRow($tableRow);
        }
        $table->render();
        return 0;
    }

    private function getTableRow(int $index, string $task, int $count): array
    {
        return ["#$index", $task, $count];
    }
}
