<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Domain\Model\Time;
use Simtt\Domain\TimeTracker;
use Simtt\Infrastructure\Service\LogFileFinder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class ContinueCommand extends Command
{

    protected static $defaultName = 'continue';

    /** @var TimeTracker */
    private $timeTracker;

    /** @var LogFileFinder */
    private $logFileFinder;

    public function __construct(TimeTracker $timeTracker, LogFileFinder $logFileFinder)
    {
        parent::__construct();

        $this->timeTracker = $timeTracker;
        $this->logFileFinder = $logFileFinder;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Continues last stopped task');
        $this->addArgument('time', InputArgument::OPTIONAL, 'time format: hh:mm or hhmm');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lastLog = $this->timeTracker->getLastLogEntry();
        if (!$lastLog || !$lastLog->stopTime) {
            $output->writeln('No stopped timer found');
            return 0;
        }

        $startTime = $this->getTime($input);
        try {
            $logEntry = $this->timeTracker->continueTimer($startTime);
            $this->getLogFile()->saveLog($logEntry);
            $output->writeln("Timer continued on {$logEntry->startTime} for '{$logEntry->task}'");
        }
        catch (\Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
        }


        return 0;
    }

    private function getTime(InputInterface $input): ?Time
    {
        $time = $input->getArgument('time');
        if ($time && $this->isTime($time)) {
            return new Time($time);
        }
        return null;
    }

    private function isTime(string $time): bool
    {
        return PatternProvider::isTime($time);
    }

    private function getLogFile(): \Simtt\Domain\Model\LogFileInterface
    {
        return $this->logFileFinder->getLogFileForDate(new \DateTime());
    }
}
