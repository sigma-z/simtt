<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   19.12.19
 */

namespace Test\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Simtt\Domain\Model\Time;
use Simtt\Infrastructure\Service\LogFile;
use Test\Helper\LogEntryCreator;
use Test\Helper\VirtualFileSystemTrait;

class LogFileTest extends TestCase
{
    use VirtualFileSystemTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFileSystem();
    }

    public function testSaveToEmptyLog(): void
    {
        $logEntry = LogEntryCreator::create('800', '830', 'task #1', 'comment #1');

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logFile->saveLog($logEntry);

        $file = $logFile->getFile();

        self::assertTrue($logEntry->isPersisted());
        self::assertFileExists($file);
        self::assertStringEqualsFile($file, $logEntry . "\n");
    }

    public function testSaveToExistingLog(): void
    {
        $logFile = LogFile::createTodayLogFile(LOG_DIR);

        $lastLogEntry = LogEntryCreator::create('900', '930', 'task #1', 'comment #1');
        $logFile->saveLog($lastLogEntry);

        $currentLogEntry = LogEntryCreator::create('930', '1030', 'task #2', 'comment #2');
        $logFile->saveLog($currentLogEntry);

        self::assertTrue($lastLogEntry->isPersisted());
        self::assertTrue($currentLogEntry->isPersisted());
        self::assertStringEqualsFile($logFile->getFile(), $lastLogEntry . "\n" . $currentLogEntry . "\n");
    }

    public function testUpdateStopTimeForLogEntry(): void
    {
        $logFile = LogFile::createTodayLogFile(LOG_DIR);

        $lastLogEntry = LogEntryCreator::create('900', '930', 'task #1', 'comment #1');
        $logFile->saveLog($lastLogEntry);

        $currentLogEntry = LogEntryCreator::create('930', '1030', 'task #2', 'comment #2');
        $logFile->saveLog($currentLogEntry);

        $currentLogEntry->stopTime = new Time('11:15');
        $logFile->saveLog($currentLogEntry);

        self::assertStringEqualsFile($logFile->getFile(), $lastLogEntry . "\n" . $currentLogEntry . "\n");
    }

    public function testUpdateStartTimeForLogEntry(): void
    {
        $logFile = LogFile::createTodayLogFile(LOG_DIR);

        $lastLogEntry = LogEntryCreator::create('900', '930', 'task #1', 'comment #1');
        $logFile->saveLog($lastLogEntry);

        $currentLogEntry = LogEntryCreator::create('930', '1030', 'task #2', 'comment #2');
        $logFile->saveLog($currentLogEntry);

        $currentLogEntry->startTime = new Time('9:45');
        $logFile->saveLog($currentLogEntry);
        $currentLogEntry->startTime = new Time('9:50');
        $logFile->saveLog($currentLogEntry);

        self::assertStringEqualsFile($logFile->getFile(), $lastLogEntry . "\n" . $currentLogEntry . "\n");
    }

}
