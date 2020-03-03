<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Application\Prompter\PrompterInterface;
use Simtt\Domain\LogFileFinderInterface;
use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\LogFileInterface;
use Simtt\Domain\Model\RecentTask;
use Simtt\Domain\Model\Time;
use Simtt\Domain\TimeTracker;
use Simtt\Infrastructure\Service\RecentTaskList;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
abstract class TimerCommand extends Command
{

    /** @var LogFileInterface */
    private $logFile;

    /** @var RecentTaskList */
    private $recentTaskList;

    /** @var TimeTracker */
    private $timeTracker;

    /** @var PrompterInterface */
    private $prompter;

    abstract protected function getMessageForActionPerformed(LogEntry $logEntry, bool $isPersisted, InputInterface $input): string;

    public function __construct(
        LogFileFinderInterface $logFileFinder,
        TimeTracker $timeTracker,
        RecentTaskList $recentTaskList,
        PrompterInterface $prompter
    ) {
        parent::__construct();
        $this->logFile = $logFileFinder->getLogFileForDate(new \DateTime());
        $this->timeTracker = $timeTracker;
        $this->prompter = $prompter;
        $this->recentTaskList = $recentTaskList;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('time', InputArgument::OPTIONAL, 'time format: hh:mm or hhmm');
        $this->addArgument('taskName', InputArgument::OPTIONAL, 'task name', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->prompter->setOutput($output);
        try {
            $logEntry = $this->performAction($input);
            $isPersisted = $logEntry->isPersisted();
            $this->logFile->saveLog($logEntry);
            $message = $this->getMessageForActionPerformed($logEntry, $isPersisted, $input);
            $output->writeln($message);
        }
        catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
        }

        return 0;
    }

    private function getTime(InputInterface $input): ?Time
    {
        $time = $input->getArgument('time');
        if ($time) {
            if ($this->isTime($time)) {
                return new Time($time);
            }
            $input->setArgument('taskName', $time);
        }
        return null;
    }

    protected function isUpdate(InputInterface $input): bool
    {
        return strpos($input->getArgument('command'), '*') !== false;
    }

    private function performAction(InputInterface $input): LogEntry
    {
        $time = $this->getTime($input);
        $taskName = $this->readTask($input);
        $comment = $this->readComment($input);
        $command = static::$defaultName;
        $callable = $this->isUpdate($input)
            ? [$this->timeTracker, 'update' . $command]
            : [$this->timeTracker, $command];
        return $callable($time, $taskName, $comment);
    }

    private function isTime(string $time): bool
    {
        return PatternProvider::isTime($time);
    }

    private function shouldPromptForTask(InputInterface $input): bool
    {
        if (!$input->isInteractive()) {
            return false;
        }
        $taskName = $input->getArgument('taskName');
        if ($taskName === '') {
            if (static::$defaultName === 'stop' || $this->isUpdate($input)) {
                $lastLog = $this->timeTracker->getLastLogEntry();
                return $lastLog && $lastLog->task === '';
            }
            return true;
        }
        return false;
    }

    private function shouldPromptForComment(InputInterface $input): bool
    {
        if (!$input->isInteractive()) {
            return false;
        }
        if (static::$defaultName === 'stop' || $this->isUpdate($input)) {
            $lastLog = $this->timeTracker->getLastLogEntry();
            return $lastLog && $lastLog->comment === '';
        }
        return true;
    }

    /**
     * @param InputInterface $input
     * @return string|string[]|null
     */
    private function readTask(InputInterface $input)
    {
        if ($this->shouldPromptForTask($input)) {
            $tasks = $this->writeRecentTasks();
            $taskName = $this->prompter->prompt('task> ');
            if (strpos($taskName, '#') === 0) {
                /** @var int|string $index */
                $index = substr($taskName, 1);
                if (isset($tasks[$index])) {
                    $taskName = $tasks[$index]->getTask();
                }
            }
        }
        else {
            $taskName = $input->getArgument('taskName');
        }
        return $taskName;
    }

    /**
     * @return RecentTask[]
     */
    private function writeRecentTasks(): array
    {
        $tasks = $this->recentTaskList->getTasks();
        $output = $this->prompter->getOutput();
        $length = strlen((string)count($tasks));
        foreach ($tasks as $index => $task) {
            $indexFormatted = "#$index" . str_repeat(' ', $length - strlen((string)$index));
            $output->writeln($indexFormatted . ' ' . $task->getTask());
        }
        return $tasks;
    }

    private function readComment(InputInterface $input): string
    {
        return $this->shouldPromptForComment($input)
            ? $this->prompter->prompt('comment> ')
            : '';
    }
}
