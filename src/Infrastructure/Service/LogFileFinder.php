<?php
declare(strict_types=1);

namespace Simtt\Infrastructure\Service;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogFileFinder
{

    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getLastLogFile(): ?LogFile
    {
        $logFiles = $this->getLogFiles();
        return end($logFiles) ?: null;
    }

    public function getLogFileForDate(\DateTime $dateTime): string
    {
        $file = $dateTime->format('Y/m/Y-m-d') . '.log';
        return $this->path . '/' . $file;
    }

    /**
     * @return LogFile[]
     */
    public function getLogFiles(): array
    {
        $iterator = $this->getLogFileIterator();
        $files = [];
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            $files[] = new LogFile($file->getPathname());
        }
        return $files;
    }

    public function getCurrentLogFile(): string
    {

    }

    private function getLogFileIterator(): \RegexIterator
    {
        $pattern = '/\d{4}-\d{2}-\d{2}\.log$/';
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
