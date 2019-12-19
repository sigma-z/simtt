<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service;

use Simtt\Domain\Model\LogEntry;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogPersister
{

    /** @var string */
    private $logDir;

    public function __construct(string $logDir)
    {
        $this->logDir = $logDir;
    }

    public function saveLog(LogEntry $logEntry): void
    {
        $fh = $this->getFileHandle();
        fwrite($fh, (string)$logEntry);
        fclose($fh);
    }

    private function getFileHandle()
    {
        $file = $this->getFile();
        $dir = dirname($file);
        $this->createDirectory($dir);
        return fopen($file, 'ab');
    }

    public function getFile(): string
    {
        $date = date('Y-m-d');
        [$year, $month, ] = explode('-', $date);
        return "$this->logDir/$year/$month/$date.log";
    }

    private function createDirectory(string $dir): void
    {
        if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }
}
