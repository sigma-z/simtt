<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service;

use Simtt\Domain\Model\LogEntry;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogHandler
{

    /**
     * @return LogEntry[]
     */
    public function getAllLogs(): array
    {
        return [];
    }

    public function getLastLog(): ?LogEntry
    {
        return null;
    }

    public function getCurrentLog(): ?LogEntry
    {
        return null;
    }

    public function addLog(LogEntry $logEntry): void
    {

    }
}
