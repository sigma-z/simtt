<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Application\Command\Helper\LogAggregationTable;
use Simtt\Application\Command\Helper\LogTable;
use Simtt\Domain\LogHandlerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Week extends Command
{

    protected static $defaultName = 'week';

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

        $this->setDescription('Show logged time information for the given week');
        $this->addArgument('offset', InputArgument::OPTIONAL, 'offset from current week');
        $this->addArgument('sum', InputArgument::OPTIONAL, 'flag to show the summarize');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->processInputArguments($input);

        $datePeriod = $this->getDatePeriod($input->getArgument('offset'));
        $entries = $this->getLogEntries($datePeriod);

        if (!$entries) {
            $startDateFormatted = $datePeriod->start->format('Y-m-d');
            $endDateFormatted = $datePeriod->end->format('Y-m-d');
            $output->writeln("No entries found from $startDateFormatted to $endDateFormatted");
            return 0;
        }

        if ($input->getArgument('sum') === 'sum') {
            $logAggTable = new LogAggregationTable(new Table($output));
            $logAggTable->processLogEntries($entries);
            $logAggTable->render();
        }
        else {
            $logTable = new LogTable(new Table($output));
            $logTable->processLogEntries($entries);
            $logTable->render();
        }

        return 0;
    }

    private function getDatePeriod($offset): \DatePeriod
    {
        $startDate = $this->getDateTime($offset);
        $endDate = clone($startDate);
        $endDate->add(new \DateInterval('P7D'));
        return new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);
    }

    private function getDateTime($offset): \DateTime
    {
        $dateTime = new \DateTime();
        if ($dateTime->format('w') !== '1') {
            $dateTime->modify('last monday');
        }

        if (is_numeric($offset)) {
            $dateTime->sub(new \DateInterval("P{$offset}W"));
        }
        return $dateTime;
    }

    private function processInputArguments(InputInterface $input): void
    {
        $offset = $input->getArgument('offset');
        if ($offset && !is_numeric($offset)) {
            $input->setArgument('offset', null);
            $input->setArgument('sum', $offset);
        }
    }

    public function getLogEntries(\DatePeriod $datePeriod): array
    {
        $logFileFinder = $this->logHandler->getLogFileFinder();
        $entries = [];
        /** @var \DateTime $day */
        foreach ($datePeriod as $day) {
            $logFile = $logFileFinder->getLogFileForDate($day);
            $entries = array_merge($entries, $logFile->getEntries());
        }
        return $entries;
    }
}
