<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;
use Simtt\Domain\TimeTracker;
use Simtt\Infrastructure\Service\LogFile;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
abstract class TimerCommand extends Command
{

    /** @var LogFile */
    protected $logFile;

    /** @var TimeTracker */
    protected $timeTracker;

    abstract protected function getMessageForActionPerformed(LogEntry $logEntry, bool $isPersisted, InputInterface $input): string;

    public function __construct(LogFile $logFile, TimeTracker $timeTracker)
    {
        parent::__construct();
        $this->logFile = $logFile;
        $this->timeTracker = $timeTracker;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('time', InputArgument::OPTIONAL, 'time format: hh:mm or hhmm');
        $this->addArgument('taskTitle', InputArgument::OPTIONAL, 'task title', '');
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
            $input->setArgument('taskTitle', $time);
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
        $taskName = $input->getArgument('taskTitle');
        $command = static::$defaultName;
        $callable = $this->isUpdate($input)
            ? [$this->timeTracker, 'update' . $command]
            : [$this->timeTracker, $command];
        return $callable($time, $taskName);
    }

    private function isTime(string $time): bool
    {
        return PatternProvider::isTime($time);
    }

}
