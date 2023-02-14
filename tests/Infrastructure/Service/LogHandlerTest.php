<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   24.12.19
 */

namespace Test\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Simtt\Infrastructure\Service\LogFileFinder;
use Simtt\Infrastructure\Service\LogHandler;
use Test\Helper\LogEntryCreator;
use Test\Helper\VirtualFileSystemTrait;

class LogHandlerTest extends TestCase
{
    use VirtualFileSystemTrait;

    /** @var LogHandler */
    private $logHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFileSystem();
        $this->logHandler = new LogHandler(new LogFileFinder(LOG_DIR));
    }

    public function testGetAllLogs(): void
    {
        LogEntryCreator::setUpLogFile('2019-11-30', [
            '12:00;     ;"";""',
            '12:30;     ;"";""'
        ]);
        LogEntryCreator::setUpLogFile('2019-12-24', [
            '22:20;     ;"";""',
            '22:25;     ;"";""'
        ]);
        LogEntryCreator::setUpLogFile('2019-12-25', [
            '12:20;     ;"";""',
            '12:30;     ;"";""'
        ]);

        $logEntries = $this->logHandler->getAllLogs();
        self::assertCount(6, $logEntries);
    }

    public function testGetLogOnEmptyLogReturnsNull(): void
    {
        $beforeLastLog = $this->logHandler->getLogReverseIndex(1);
        $lastLog = $this->logHandler->getLastLog();
        self::assertNull($beforeLastLog);
        self::assertNull($lastLog);
    }

    public function testGetLogForYesterdayReturnsNull(): void
    {
        LogEntryCreator::setUpLogFileYesterday([
            '12:00;     ;"";""',
            '12:30;     ;"";""'
        ]);
        $beforeLastLog = $this->logHandler->getLogReverseIndex(1);
        $lastLog = $this->logHandler->getLastLog();
        self::assertNull($beforeLastLog);
        self::assertNull($lastLog);
    }

    public function testGetLogForStartedTimerReturnsNull(): void
    {
        LogEntryCreator::setUpLogFileToday([
            '12:00;     ;"";""'
        ]);
        $beforeLastLog = $this->logHandler->getLogReverseIndex(1);
        $lastLog = $this->logHandler->getLastLog();
        self::assertNull($beforeLastLog);
        self::assertNotNull($lastLog);
        self::assertSame('12:00', (string)$lastLog->startTime);
    }

    public function testGetLogForStoppedTimerReturnsObject(): void
    {
        LogEntryCreator::setUpLogFileToday([
            '12:00;12:30;"";""'
        ]);
        $beforeLastLog = $this->logHandler->getLogReverseIndex(1);
        $lastLog = $this->logHandler->getLastLog();
        self::assertNull($beforeLastLog);
        self::assertNotNull($lastLog);
        self::assertSame('12:30', (string)$lastLog->stopTime);
    }
}
