<?php
declare(strict_types=1);

namespace Simtt\Application\Command\Helper;

use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\LogFileInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogFileEntriesFetcher
{

    /** @var LogFileInterface[] */
    private $logFiles;

    /**
     * @param LogFileInterface[] $logFiles
     */
    public function __construct(array $logFiles)
    {
        $this->logFiles = $logFiles;
    }

    /**
     * @return LogEntry[]|null
     */
    public function fetch(): ?array
    {
        $logFile = array_pop($this->logFiles);
        return $logFile ? $logFile->getEntries() : null;
    }

}
