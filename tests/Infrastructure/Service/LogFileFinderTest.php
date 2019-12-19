<?php
declare(strict_types=1);

namespace Test\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Simtt\Infrastructure\Service\LogFileFinder;
use Test\Helper\VirtualFileSystemTrait;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class LogFileFinderTest extends TestCase
{

    use VirtualFileSystemTrait;

    private const LOG_PATH = 'vfs://logs';

    /** @var array */
    private static $structure = [
        '2019/11' => [
            '2019-11-19.log' => '',
            '2019-11-25.log' => '',
            'some_other-file' => '',
            '2019-11-26.log' => [],
        ],
        '2019/12' => [
            '2019-12-02.log' => ''
        ],
        '2020/01' => [
            '2020-01-02.log' => ''
        ]
    ];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::createFileStructure();
    }

    public function testGetLogFiles(): void
    {
        $logFileFinder = new LogFileFinder(self::LOG_PATH);
        $actualFiles = $logFileFinder->getLogFiles();
        $expectedFiles = self::getExpectedLogFiles();
        self::assertSame($expectedFiles, $actualFiles);
    }

    public function testLastLogFile(): void
    {
        $logFileFinder = new LogFileFinder(self::LOG_PATH);
        $actualFile = $logFileFinder->getLastLogFile();
        self::assertSame(self::LOG_PATH . '/2020/01/2020-01-02.log', $actualFile);
    }

    public function testGetLogFileForDate(): void
    {
        $logFileFinder = new LogFileFinder(self::LOG_PATH);
        $date = new \DateTime('2013-07-01');
        $actualFile = $logFileFinder->getLogFileForDate($date);
        self::assertSame(self::LOG_PATH . '/2013/07/2013-07-01.log', $actualFile);
    }

    private static function createFileStructure(): void
    {
        foreach (self::$structure as $path => $files) {
            mkdir(self::LOG_PATH . '/' . $path, 0777, true);
            foreach ($files as $file => $content) {
                if (is_string($content)) {
                    file_put_contents(self::LOG_PATH . '/' . $path . '/' . $file, $content);
                }
            }
        }
    }

    private static function getExpectedLogFiles(): array
    {
        $expectedFiles = [];
        foreach (self::$structure as $path => $files) {
            foreach ($files as $file => $content) {
                if (is_string($content) && substr($file, -4) === '.log') {
                    $expectedFiles[] = self::LOG_PATH . '/' . $path . '/' . $file;
                }
            }
        }
        return $expectedFiles;
    }
}
