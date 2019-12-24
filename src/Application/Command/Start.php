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
        $this->addArgument('startTime', InputArgument::OPTIONAL, 'time format: hhmm or hmm');
        $this->addArgument('taskTitle', InputArgument::OPTIONAL, 'task title', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = $this->getStartTime($input);
        $logEntry = $this->timeTracker->start($startTime, $input->getArgument('taskTitle'));
        $this->logFile->saveLog($logEntry);


        $output->writeln($this->getName());
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
