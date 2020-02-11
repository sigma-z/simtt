<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service;

use Simtt\Domain\LogFileFinderInterface;
use Simtt\Domain\Model\LogFileInterface;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogFileFinder implements LogFileFinderInterface
{

    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getLastLogFile(): ?LogFileInterface
    {
        $logFiles = $this->getLogFiles();
        return end($logFiles) ?: null;
    }

    public function getLogFileForDate(\DateTime $dateTime): LogFileInterface
    {
        $file = $dateTime->format('Y/m/Y-m-d') . LogFile::FILE_EXT;
        return new LogFile($this->path . '/' . $file);
    }

    /**
     * @return LogFileInterface[]
     */
    public function getLogFiles(): array
    {
        $iterator = $this->getLogFileIterator();
        $files = [];
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            $files[] = new LogFile($file->getPathname());
        }
        sort($files);
        return $files;
    }

    private function getLogFileIterator(): \RegexIterator
    {
        $pattern = '/\d{4}-\d{2}-\d{2}' . preg_quote(LogFile::FILE_EXT, '/') . '$/';
        $directoryIterator = new \RecursiveDirectoryIterator($this->path, \FilesystemIterator::SKIP_DOTS);
        $iteratorIterator = new \RecursiveIteratorIterator($directoryIterator);
        $filterIterator = new \CallbackFilterIterator($iteratorIterator, static function (\SplFileInfo $file) {
            yield $file->isFile();
        });
        return new \RegexIterator($filterIterator, $pattern);
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
