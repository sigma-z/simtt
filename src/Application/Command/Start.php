<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Application\Config\Config;
use Simtt\Domain\Model\Time;
use Simtt\Domain\TimeTracker;
use Simtt\Infrastructure\Service\LogHandler;
use Simtt\Infrastructure\Service\LogPersister;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Start extends Command
{

    protected static $defaultName = 'start';

    /** @var LogPersister */
    private $logPersister;

    /** @var TimeTracker */
    private $timeTracker;

    public function __construct(LogPersister $logPersister = null, TimeTracker $timeTracker = null)
    {
        parent::__construct();
        $this->logPersister = $logPersister ?: new LogPersister((new Config())->getLogDir());
        $this->timeTracker = $timeTracker ?: new TimeTracker(new LogHandler());
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
        $this->logPersister->saveLog($logEntry);


        //$output->writeln($this->getName());
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
