<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   22.12.19
 */

namespace Test\Application\Command;

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
    }

    protected function tearDown(): void
    {
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
        LogEntryCreator::setUpLogFileToday([
            $logEntryOne
        ]);
        $output = $this->runCommand('start 930');
        self::assertSame('Timer started at 09:30', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntryTwo = (string)LogEntryCreator::create('9:30');
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
        LogEntryCreator::setUpLogFileToday([
            $logEntryOne
        ]);
        $output = $this->runCommand('start 10:30');
        self::assertSame('Timer started at 10:30', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntryTwo = LogEntryCreator::create('10:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntryOne . "\n" . $logEntryTwo . "\n");
    }
}
