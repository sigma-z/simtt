<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Domain\Model\Time;
use Simtt\Domain\TimeTracker;
use Simtt\Infrastructure\Service\LogFile;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Start extends Command
{

    protected static $defaultName = 'start';

    /** @var LogFile */
    private $logFile;

    /** @var TimeTracker */
    private $timeTracker;

    public function __construct(LogFile $logFile, TimeTracker $timeTracker)
    {
        parent::__construct();
        $this->logFile = $logFile;
        $this->timeTracker = $timeTracker;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Starts a timer');
        $this->addArgument('startTime', InputArgument::OPTIONAL, 'time format: hh:mm or hhmm');
        $this->addArgument('taskTitle', InputArgument::OPTIONAL, 'task title', '');
    }

    /**
     * @TODO missing case of updating the task title
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = $this->getStartTime($input);
        $taskName = $input->getArgument('taskTitle');
        $logEntry = $this->timeTracker->start($startTime, $taskName);
        $isPersisted = $logEntry->isPersisted();
        $this->logFile->saveLog($logEntry);
        $message = $isPersisted
            ? 'Timer start updated to ' . $logEntry->startTime
            : 'Timer started at ' . $logEntry->startTime;
        if ($logEntry->task) {
            $message .= " for '{$logEntry->task}'";
        }
        $output->writeln($message);

        return 0;
    }

    private function getStartTime(InputInterface $input): ?Time
    {
        $startTime = $input->getArgument('startTime');
        if ($startTime) {
            return new Time($startTime);
        }
        return null;
    }

}
