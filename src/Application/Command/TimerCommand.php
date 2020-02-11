<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Application\Prompter\PrompterInterface;
use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\LogFileInterface;
use Simtt\Domain\Model\Time;
use Simtt\Domain\TimeTracker;
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

    /** @var TimeTracker */
    private $timeTracker;

    /** @var PrompterInterface */
    private $prompter;

    abstract protected function getMessageForActionPerformed(LogEntry $logEntry, bool $isPersisted, InputInterface $input): string;

    public function __construct(LogFileInterface $logFile, TimeTracker $timeTracker, PrompterInterface $prompter)
    {
        parent::__construct();
        $this->logFile = $logFile;
        $this->timeTracker = $timeTracker;
        $this->prompter = $prompter;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('time', InputArgument::OPTIONAL, 'time format: hh:mm or hhmm');
        $this->addArgument('taskName', InputArgument::OPTIONAL, 'task name', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
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
        $taskName = $this->shouldPromptForTask($input)
            ? $this->prompter->prompt('task> ')
            : $input->getArgument('taskName');
        $comment = $this->shouldPromptForComment($input)
            ? $this->prompter->prompt('comment> ')
            : '';
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
                $lastLog = $this->timeTracker->getLogHandler()->getLastLog();
                return $lastLog && $lastLog->task === '';
            }
            return true;
        }
        return false;
    }

    private function shouldPromptForComment(InputInterface $input): bool
    {
        return $input->isInteractive();
    }

}
