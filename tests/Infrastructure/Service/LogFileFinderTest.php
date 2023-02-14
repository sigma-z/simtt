<?php
declare(strict_types=1);

namespace Test\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Simtt\Infrastructure\Service\LogFile;
use Simtt\Infrastructure\Service\LogFileFinder;
use Test\Helper\VirtualFileSystemTrait;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogFileFinderTest extends TestCase
{
    use VirtualFileSystemTrait;

    /** @var array */
    private static $structure = [
        '2019/11' => [
            '2019-11-19' . LogFile::FILE_EXT => '',
            '2019-11-25' . LogFile::FILE_EXT => '',
            'some_other-file' => '',
            '2019-11-26' . LogFile::FILE_EXT => [],
        ],
        '2019/12' => [
            '2019-12-02' . LogFile::FILE_EXT => ''
        ],
        '2020/01' => [
            '2020-01-02' . LogFile::FILE_EXT => ''
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFileSystem();
        self::createFileStructure();
    }


    public function testGetLogFiles(): void
    {
        $logFileFinder = new LogFileFinder(LOG_DIR);
        $actualLogFiles = $logFileFinder->getLogFiles();
        $expectedFiles = self::getExpectedLogFiles();
        $actualFiles = array_map(static function (LogFile $logFile) {
            return $logFile->getFile();
        }, $actualLogFiles);
        self::assertSame($expectedFiles, $actualFiles);
    }

    public function testLastLogFile(): void
    {
        $logFileFinder = new LogFileFinder(LOG_DIR);
        $actualLogFile = $logFileFinder->getLastLogFile();
        self::assertSame(
            static::getFile(LOG_DIR . '/2020/01/2020-01-02' . LogFile::FILE_EXT),
            $actualLogFile->getFile()
        );
    }

    public function testGetLogFileForDate(): void
    {
        $logFileFinder = new LogFileFinder(LOG_DIR);
        $date = new \DateTime('2013-07-01');
        $actualFile = $logFileFinder->getLogFileForDate($date)->getFile();
        self::assertSame(LOG_DIR . '/2013/07/2013-07-01' . LogFile::FILE_EXT, $actualFile);
    }

    private static function createFileStructure(): void
    {
        foreach (self::$structure as $path => $files) {
            mkdir(LOG_DIR . '/' . $path, 0777, true);
            foreach ($files as $file => $content) {
                if (is_string($content)) {
                    file_put_contents(LOG_DIR . '/' . $path . '/' . $file, $content);
                }
            }
        }
    }

    private static function getExpectedLogFiles(): array
    {
        $expectedFiles = [];
        foreach (self::$structure as $path => $files) {
            foreach ($files as $file => $content) {
                if (is_string($content) && substr($file, -4) === LogFile::FILE_EXT) {
                    $expectedFiles[] = self::getFile(LOG_DIR . '/' . $path . '/' . $file);
                }
            }
        }
        return $expectedFiles;
    }

    private static function getFile($file): string
    {
        if ('Linux' === PHP_OS) {
            return $file;
        }
        $parts = explode('://', $file);
        return $parts[0] . '://' . str_replace('/', DIRECTORY_SEPARATOR, $parts[1]);
    }
}
