<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Infrastructure\Service\RecentTaskList;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Tasks extends Command
{

    protected static $defaultName = 'tasks';

    /** @var RecentTaskList */
    private $recentTaskList;

    /** @var int */
    private $showTasksDefault;

    public function __construct(RecentTaskList $recentTaskList, int $showTasksDefault)
    {
        parent::__construct();

        $this->recentTaskList = $recentTaskList;
        $this->showTasksDefault = $showTasksDefault;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Lists recent logged tasks');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tasks = $this->recentTaskList->getTasks();
        if (count($tasks) === 0) {
            $output->writeln('No entries found');
            return 0;
        }

        $table = new Table($output);
        $table->setHeaders(['#', 'Task', 'Count']);
        foreach ($tasks as $index => $recentTask) {
            if ($index >= $this->showTasksDefault) {
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
