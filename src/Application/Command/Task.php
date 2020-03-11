<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Application\Prompter\PrompterInterface;
use Simtt\Application\Task\RecentTasksPrinterInterface;
use Simtt\Application\Task\TaskPrompterInterface;
use Simtt\Domain\LogHandlerInterface;
use Simtt\Domain\Model\LogEntry;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Task extends PropertyUpdateCommand
{

    protected static $defaultName = 'task';

    /** @var RecentTasksPrinterInterface */
    private $recentTasksPrinter;

    /** @var TaskPrompterInterface */
    private $taskPrompter;

    public function __construct(
        LogHandlerInterface $logHandler,
        PrompterInterface $prompter,
        RecentTasksPrinterInterface $recentTasksPrinter,
        TaskPrompterInterface $taskPrompter
    ) {
        parent::__construct($logHandler, $prompter);
        $this->recentTasksPrinter = $recentTasksPrinter;
        $this->taskPrompter = $taskPrompter;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Sets task name for current log entry by offset, if given');
        $this->addArgument('offset', InputArgument::OPTIONAL, 'offset from last log entry of today', 0);
        $this->addArgument('task', InputArgument::OPTIONAL, 'task name');
    }

    protected function processInputArguments(InputInterface $input): void
    {
        $offset = $input->getArgument('offset');
        if ($offset && !is_numeric($offset) && !$input->getArgument('task')) {
            $input->setArgument('offset', 0);
            $input->setArgument('task', $offset);
        }
    }

    protected function getMessageForActionPerformed(LogEntry $logEntry): string
    {
        return "Updated task '{$logEntry->task}' for log started at {$logEntry->startTime}";
    }

    protected function updateLogEntry(InputInterface $input, LogEntry $logEntry): void
    {
        $taskName = $input->getArgument('task');
        if ($taskName === null) {
            $tasks = $this->recentTasksPrinter->outputTasks($this->prompter->getOutput());
            $taskName = $this->taskPrompter->promptTask($tasks, $this->prompter);
        }
        $logEntry->task = $taskName;
    }
}
