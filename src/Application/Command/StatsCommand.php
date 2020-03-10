<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Application\Command\Helper\LogAggregationTable;
use Simtt\Application\Command\Helper\LogTable;
use Simtt\Domain\LogHandlerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
abstract class StatsCommand extends Command
{

    /** @var LogHandlerInterface */
    protected $logHandler;

    abstract protected function getDatePeriod($offset): \DatePeriod;

    public function __construct(LogHandlerInterface $logHandler)
    {
        parent::__construct();

        $this->logHandler = $logHandler;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->processInputArguments($input);

        $datePeriod = $this->getDatePeriod($input->getArgument('offset'));
        $entries = $this->getLogEntries($datePeriod);

        if (!$entries) {
            if (static::$defaultName === 'day') {
                $output->writeln('No entries found for ' . $this->getFormattedDate($datePeriod->getStartDate()));
            }
            else {
                $startDateFormatted = $datePeriod->start->format('Y-m-d');
                $endDateFormatted = $datePeriod->end->format('Y-m-d');
                $output->writeln("No entries found from $startDateFormatted to $endDateFormatted");
            }
            return 0;
        }

        $this->renderTables($input, $output, $entries);
        return 0;
    }

    private function getFormattedDate(\DateTimeInterface $dateTime): string
    {
        $today = new \DateTime();
        $formattedDate = $dateTime->format('Y-m-d');
        if ($formattedDate === $today->format('Y-m-d')) {
            return 'today';
        }
        if ($formattedDate === $today->sub(new \DateInterval('P1D'))->format('Y-m-d')) {
            return 'yesterday';
        }
        return $formattedDate;
    }

    protected function processInputArguments(InputInterface $input): void
    {
        $offset = $input->getArgument('offset');
        if ($offset && !is_numeric($offset)) {
            $input->setArgument('offset', null);
            $input->setArgument('sum', $offset);
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param array           $entries
     */
    protected function renderTables(InputInterface $input, OutputInterface $output, array $entries): void
    {
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
    }

    protected function getLogEntries(\DatePeriod $datePeriod): array
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
