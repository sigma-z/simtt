<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Domain\LogHandlerInterface;
use Simtt\Domain\Model\LogEntry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Status extends Command
{

    protected static $defaultName = 'status';

    /** @var LogHandlerInterface */
    private $logHandler;

    public function __construct(LogHandlerInterface $logHandler)
    {
        parent::__construct();

        $this->logHandler = $logHandler;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Status command shows if a timer is running');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logEntry = $this->logHandler->getLastLog();
        if ($logEntry) {
            $this->outputLogEntryStatus($logEntry, $output);
            return 0;
        }

        $output->writeln('STATUS: No timer is running.');
        return 0;
    }

    private function outputLogEntryStatus(LogEntry $logEntry, OutputInterface $output): void
    {
        if ($logEntry->stopTime === null) {
            $output->writeln('STATUS: Timer started at ' . $logEntry->startTime);
        }
        else {
            $timeRange = $logEntry->startTime . ' - ' . $logEntry->stopTime;
            $timeDiff = $logEntry->diff();
            if ($timeDiff) {
                $timeRange .= " (={$timeDiff})";
            }
            $output->writeln('STATUS: Last timer ran from ' . $timeRange);
        }
        $output->writeln('Task: ' . ($logEntry->task ?: '-'));
        if ($logEntry->comment) {
            $output->writeln('Comment: ' . $logEntry->comment);
        }
    }

}
