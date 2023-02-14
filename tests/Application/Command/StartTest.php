<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   22.12.19
 */

namespace Test\Application\Command;

use Helper\DIContainer;
use Simtt\Infrastructure\Prompter\Prompter;
use Simtt\Infrastructure\Service\Clock\FixedClock;
use Simtt\Infrastructure\Service\LogFile;
use Test\Helper\LogEntryCreator;
use Test\Helper\VirtualFileSystemTrait;

class StartTest extends TestCase
{
    use VirtualFileSystemTrait;

    protected function getCommandShortName(): string
    {
        return 'start.cmd';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFileSystem();
        DIContainer::$container->set('prompter', Prompter::create());
        DIContainer::$container->set('clock', new FixedClock(new \DateTime('12:00:00')));
    }

    public function testStart(): void
    {
        $output = $this->runCommand('start 930');
        self::assertSame('Timer started at 09:30', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartRealNow(): void
    {
        $output = $this->runCommand('start');
        self::assertStringStartsWith('Timer started at ', rtrim($output->fetch()));
    }

    public function testStartNow(): void
    {
        $output = $this->runCommand('start');
        self::assertSame('Timer started at 12:00', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntry = LogEntryCreator::create('12:00');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartNowWithTask(): void
    {
        $output = $this->runCommand('start "123|654"');
        self::assertSame("Timer started at 12:00 for '123|654'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntry = LogEntryCreator::create('12:00', '', '123|654');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testUpdateStart(): void
    {
        $output = $this->runCommand('start* 930');
        self::assertSame('Timer started at 09:30', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
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

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartNewLog(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start 930');
        self::assertSame('Timer started at 09:30', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntryTwo = (string)LogEntryCreator::create('9:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntryOne . "\n" . $logEntryTwo . "\n");
    }

    public function testStartNewLogNow(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start');
        self::assertSame('Timer started at 12:00', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntryTwo = (string)LogEntryCreator::create('12:00');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntryOne . "\n" . $logEntryTwo . "\n");
    }

    public function testStartNewLogNowWithTask(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start "task"');
        self::assertSame("Timer started at 12:00 for 'task'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntryTwo = (string)LogEntryCreator::create('12:00', '', 'task');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntryOne . "\n" . $logEntryTwo . "\n");
    }

    public function testStartWithTaskName(): void
    {
        $output = $this->runCommand('start 930 task');
        self::assertSame("Timer started at 09:30 for 'task'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30', '', 'task');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartUpdateWithTaskNameWillBeOverwritten(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'test task')
        ]);
        $output = $this->runCommand('start* 930 task');
        self::assertSame("Timer start updated to 09:30 for 'task'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30', '', 'task');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartAddsEntry(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900', '1000');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start 10:30');
        self::assertSame('Timer started at 10:30', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntryTwo = LogEntryCreator::create('10:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntryOne . "\n" . $logEntryTwo . "\n");
    }

    public function testStartBeforeLastStop(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900', '1000');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start 09:30');
        self::assertSame('Error: Stop time of last log is newer than the new start time.', rtrim($output->fetch()));
    }

    public function testStartBeforeLastStart(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start 08:30');
        self::assertSame('Error: Start time of last log is newer than the new start time.', rtrim($output->fetch()));
    }

    public function testUpdateStartBeforeLastStart(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900');
        $logEntryTwo = LogEntryCreator::createToString('1000');
        LogEntryCreator::setUpLogFileToday([$logEntryOne, $logEntryTwo]);
        $output = $this->runCommand('start* 08:30');
        self::assertSame('Error: Start time of last log is older than start time.', rtrim($output->fetch()));
    }

    public function testUpdateStartBeforeLastStop(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900', '1000');
        $logEntryTwo = LogEntryCreator::createToString('1000');
        LogEntryCreator::setUpLogFileToday([$logEntryOne, $logEntryTwo]);
        $output = $this->runCommand('start* 9:30');
        self::assertSame('Error: Stop time of last log is older than start time.', rtrim($output->fetch()));
    }

    public function testUpdateStartBeforeStop(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900', '1000');
        LogEntryCreator::setUpLogFileToday([$logEntryOne]);
        $output = $this->runCommand('start* 10:30');
        self::assertSame('Error: Stop time of last log is older than start time.', rtrim($output->fetch()));
    }

    public function testStartInInteractiveMode(): void
    {
        $prompterMock = $this->getMockBuilder(Prompter::class)->disableOriginalConstructor()->getMock();
        $prompterMock->method('prompt')
            ->willReturnCallback(static function(string $promptText) {
                return rtrim($promptText, '> ');
            });
        DIContainer::$container->set('prompter', $prompterMock);

        $output = $this->runCommandInInteractiveMode('start 930');
        self::assertSame("Timer started at 09:30 for 'task'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30', '', 'task', 'comment');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartWithTaskInInteractiveMode(): void
    {
        $prompterMock = $this->getMockBuilder(Prompter::class)->disableOriginalConstructor()->getMock();
        $prompterMock->method('prompt')
            ->willReturnCallback(static function(string $promptText) {
                return rtrim($promptText, '> ');
            });
        DIContainer::$container->set('prompter', $prompterMock);

        $output = $this->runCommandInInteractiveMode('start 930 task123');
        self::assertSame("Timer started at 09:30 for 'task123'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30', '', 'task123', 'comment');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testUpdateStartInInteractiveMode(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900')
        ]);

        $prompterMock = $this->getMockBuilder(Prompter::class)->disableOriginalConstructor()->getMock();
        $prompterMock->method('prompt')
            ->willReturnCallback(static function(string $promptText) {
                return rtrim($promptText, '> ');
            });
        DIContainer::$container->set('prompter', $prompterMock);

        $output = $this->runCommandInInteractiveMode('start* 930');
        self::assertSame("Timer start updated to 09:30 for 'task'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30', '', 'task', 'comment');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testUpdateStartWithTaskSetInInteractiveMode(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'test task')
        ]);

        $prompterMock = $this->getMockBuilder(Prompter::class)->disableOriginalConstructor()->getMock();
        $prompterMock->method('prompt')
            ->willReturnCallback(static function(string $promptText) {
                return rtrim($promptText, '> ');
            });
        DIContainer::$container->set('prompter', $prompterMock);

        $output = $this->runCommandInInteractiveMode('start* 930');
        self::assertSame("Timer start updated to 09:30 for 'test task'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30', '', 'test task', 'comment');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testUpdateStartWithTaskInInteractiveMode(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'test task')
        ]);

        $prompterMock = $this->getMockBuilder(Prompter::class)->disableOriginalConstructor()->getMock();
        $prompterMock->method('prompt')
            ->willReturnCallback(static function(string $promptText) {
                return rtrim($promptText, '> ');
            });
        DIContainer::$container->set('prompter', $prompterMock);

        $output = $this->runCommandInInteractiveMode('start* 930 task123');
        self::assertSame("Timer start updated to 09:30 for 'task123'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30', '', 'task123', 'comment');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }
}
