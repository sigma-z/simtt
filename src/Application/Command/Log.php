<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

use Simtt\Application\Command\Helper\LogTable;
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
        $this->processInputArguments($input);

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

        $orderDir = strtolower($input->getArgument('order-direction'));
        $entries = array_slice($entries, 0, $indexOfFirstEntryOutOfRange);
        $logTableHelper = new LogTable(new Table($output), $firstEntryOutOfRangeStartTime, $orderDir === self::DESC);
        $logTableHelper->processLogEntries($entries);
        $logTableHelper->render();

        return 0;
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

    private function processInputArguments(InputInterface $input): void
    {
        $selectionRange = $input->getArgument('range-selection');
        if ($selectionRange && !PatternProvider::isSelectionRangePattern($selectionRange)) {
            $input->setArgument('order-direction', $selectionRange);
        }
    }

}
