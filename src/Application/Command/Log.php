<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;
use Simtt\Infrastructure\Service\LogFile;
use Simtt\Infrastructure\Service\LogHandler;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class Log extends Command
{

    private const DESC = 'desc';
    private const ASC = 'asc';

    protected static $defaultName = 'log';

    /** @var LogHandler */
    private $logHandler;

    /** @var int */
    private $showLogItemsDefault;

    public function __construct(LogHandler $logHandler, int $showLogItemsDefault)
    {
        parent::__construct();

        $this->logHandler = $logHandler;
        $this->showLogItemsDefault = $showLogItemsDefault;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Show logged time information');
        $this->addArgument('range-selection', InputArgument::OPTIONAL, 'selection or range of days');
        $this->addArgument('order-direction', InputArgument::OPTIONAL, 'task title', self::ASC);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $selectionRange = $input->getArgument('range-selection');
        if ($selectionRange && !PatternProvider::isSelectionRangePattern($selectionRange)) {
            $input->setArgument('order-direction', $selectionRange);
        }
        $logFileFinder = $this->logHandler->getLogFileFinder();
        $dateTime = new \DateTime();
        $logFile = $logFileFinder->getLogFileForDate($dateTime);
        $entries = (new LogFile($logFile))->getEntries();
        $numberOfEntries = count($entries);
        if ($numberOfEntries === 0) {
            $output->writeln('No entries found');
            return 0;
        }

        $indexOfFirstEntryOutOfRange = $this->getIndexOfFirstEntryOutOfRange($numberOfEntries);
        $firstEntryOutOfRangeStartTime = $this->getFirstEntryOutOfRangeStartTime($entries, $indexOfFirstEntryOutOfRange);

        $entries = array_slice($entries, 0, $indexOfFirstEntryOutOfRange);
        $rows = $this->getTableRows($entries, $firstEntryOutOfRangeStartTime);
        $orderDir = strtolower($input->getArgument('order-direction'));
        if ($orderDir === self::DESC) {
            $rows = array_reverse($rows);
        }
        $this->createTable($rows, $output);

        return 0;
    }

    /**
     * @param array[]         $rows
     * @param OutputInterface $output
     */
    private function createTable(array $rows, OutputInterface $output): void
    {
        $table = new Table($output);
        $table->setHeaders(['Start', 'Stop', 'Duration', 'Task', 'Comment']);
        $table->setRows($rows);
        $table->render();
    }

    private function getTableRows(array $entries, ?Time $lastStopTime): array
    {
        $rows = [];
        foreach ($entries as $index => $entry) {
            $stopTime = isset($entries[$index + 1]) ? $entries[$index + 1]->startTime : $lastStopTime;
            $rows[] = [
                (string)$entry->startTime,
                $stopTime ? (string)$stopTime : '',
                $entry->getDuration($stopTime) ?: 'running ...',
                $entry->task,
                $entry->comment,
            ];
        }
        return $rows;
    }

    /**
     * @param LogEntry[] $entries
     * @param int        $indexOfFirstEntryOutOfRange
     * @return Time|null
     */
    private function getFirstEntryOutOfRangeStartTime(array $entries, int $indexOfFirstEntryOutOfRange): ?Time
    {
        return isset($entries[$indexOfFirstEntryOutOfRange])
            ? $entries[$indexOfFirstEntryOutOfRange]->startTime
            : null;
    }

    private function getIndexOfFirstEntryOutOfRange(int $numberOfEntries): int
    {
        return $this->showLogItemsDefault > $numberOfEntries ? $numberOfEntries : $this->showLogItemsDefault;
    }

}
