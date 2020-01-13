<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   22.12.19
 */

namespace Test\Application\Command;

use Simtt\Domain\Model\Time;
use Simtt\Infrastructure\Service\LogFile;
use Test\Helper\LogEntryCreator;
use Test\Helper\VirtualFileSystem;

class StartTest extends TestCase
{

    protected function getCommandShortName(): string
    {
        return 'start.cmd';
    }

    protected function setUp(): void
    {
        parent::setUp();
        VirtualFileSystem::setUpFileSystem();
        Time::$now = '12:00';
    }

    protected function tearDown(): void
    {
        Time::$now = 'now';
        VirtualFileSystem::tearDownFileSystem();
        parent::tearDown();
    }

    public function testStart(): void
    {
        $output = $this->runCommand('start 930');
        self::assertSame('Timer started at 09:30', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartNow(): void
    {
        $output = $this->runCommand('start');
        self::assertSame('Timer started at 12:00', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('12:00');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testUpdateStart(): void
    {
        $output = $this->runCommand('start* 930');
        self::assertSame('Timer started at 09:30', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartUpdateStart(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900')
        ]);
        $output = $this->runCommand('start* 930');
        self::assertSame('Timer start updated to 09:30', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartNewLog(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start 930');
        self::assertSame('Timer started at 09:30', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntryTwo = (string)LogEntryCreator::create('9:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntryOne . "\n" . $logEntryTwo . "\n");
    }

    public function testStartNewLogNow(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start');
        self::assertSame('Timer started at 12:00', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntryTwo = (string)LogEntryCreator::create('12:00');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntryOne . "\n" . $logEntryTwo . "\n");
    }

    public function testStartNewLogNowWithTask(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start "task"');
        self::assertSame("Timer started at 12:00 for 'task'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntryTwo = (string)LogEntryCreator::create('12:00', '', 'task');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntryOne . "\n" . $logEntryTwo . "\n");
    }

    public function testStartWithTaskTitle(): void
    {
        $output = $this->runCommand('start 930 task');
        self::assertSame("Timer started at 09:30 for 'task'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30', '', 'task');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartUpdateWithTaskTitleWillBeOverwritten(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'test task')
        ]);
        $output = $this->runCommand('start* 930 task');
        self::assertSame("Timer start updated to 09:30 for 'task'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30', '', 'task');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartAddsEntry(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900', '1000');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start 10:30');
        self::assertSame('Timer started at 10:30', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntryTwo = LogEntryCreator::create('10:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntryOne . "\n" . $logEntryTwo . "\n");
    }

    public function testStartBeforeLastStop(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900', '1000');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start 09:30');
        self::assertSame('Error: Stop time of last log is newer than the new start time!', rtrim($output->fetch()));
    }

    public function testStartBeforeLastStart(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start 08:30');
        self::assertSame('Error: Start time of last log is newer than the new start time!', rtrim($output->fetch()));
    }

    public function testUpdateStartBeforeLastStart(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900');
        $logEntryTwo = LogEntryCreator::createToString('1000');
        LogEntryCreator::setUpLogFileToday([$logEntryOne, $logEntryTwo]);
        $output = $this->runCommand('start* 08:30');
        self::assertSame('Error: Start time of last log is older than start time!', rtrim($output->fetch()));
    }

    public function testUpdateStartBeforeLastStop(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900', '1000');
        $logEntryTwo = LogEntryCreator::createToString('1000');
        LogEntryCreator::setUpLogFileToday([$logEntryOne, $logEntryTwo]);
        $output = $this->runCommand('start* 9:30');
        self::assertSame('Error: Stop time of last log is older than start time!', rtrim($output->fetch()));
    }

    public function testUpdateStartBeforeStop(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900', '1000');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start* 10:30');
        self::assertSame('Error: Stop time of last log is older than start time!', rtrim($output->fetch()));
    }
}
