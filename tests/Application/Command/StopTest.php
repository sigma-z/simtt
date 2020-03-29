<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   22.12.19
 */

namespace Test\Application\Command;

use Helper\DIContainer;
use Simtt\Domain\Model\Time;
use Simtt\Infrastructure\Prompter\Prompter;
use Simtt\Infrastructure\Service\Clock\FixedClock;
use Simtt\Infrastructure\Service\LogFile;
use Test\Helper\LogEntryCreator;
use Test\Helper\VirtualFileSystem;

class StopTest extends TestCase
{

    protected function getCommandShortName(): string
    {
        return 'stop.cmd';
    }

    protected function setUp(): void
    {
        parent::setUp();
        VirtualFileSystem::setUpFileSystem();
        DIContainer::$container->set('clock', new FixedClock(new \DateTime('12:00:00')));
    }

    protected function tearDown(): void
    {
        VirtualFileSystem::tearDownFileSystem();
        parent::tearDown();
    }

    public function testStopOnEmptyLog(): void
    {
        $output = $this->runCommand('stop 930');
        self::assertSame('Error: No log entry found!', rtrim($output->fetch()));
    }

    public function testUpdateOnEmptyLog(): void
    {
        $output = $this->runCommand('stop* 930 ');
        self::assertSame('Error: No log entry found!', rtrim($output->fetch()));
    }

    public function testStop(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900')
        ]);
        $output = $this->runCommand('stop 930');
        self::assertSame('Timer stopped at 09:30', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:00', '9:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStopRealNow(): void
    {
        Time::$now = 'now';
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('000')
        ]);
        $output = $this->runCommand('stop');
        self::assertStringStartsWith('Timer stopped at ', $output->fetch());
    }

    public function testStopNow(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900')
        ]);
        $output = $this->runCommand('stop');
        self::assertSame('Timer stopped at 12:00', rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:00', '12:00');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStopNowWithTask(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900')
        ]);
        $output = $this->runCommand('stop "123|654"');
        self::assertSame("Timer stopped at 12:00 for '123|654'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:00', '12:00', '123|654');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStopBeforeStart(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900')
        ]);
        $output = $this->runCommand('stop 830');
        self::assertSame('Error: Stop time cannot be before start time!', rtrim($output->fetch()));
    }

    public function testStopOnStopped(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('1000', '1010')
        ]);
        $output = $this->runCommand('stop 1030');
        self::assertSame("Error: Cannot stop a stopped timer, please use update stop 'stop*'", rtrim($output->fetch()));
    }

    public function testStopWithTaskName(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('1000', '', 'old task title')
        ]);
        $output = $this->runCommand('stop 1030 "new task title"');
        self::assertSame("Timer stopped at 10:30 for 'new task title'", rtrim($output->fetch()));
    }

    public function testUpdateStop(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('1000', '1100')
        ]);
        $output = $this->runCommand('stop* 1030 ');
        self::assertSame('Timer stop updated to 10:30', rtrim($output->fetch()));
    }

    public function testUpdateStopBeforeStart(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('1000', '1100')
        ]);
        $output = $this->runCommand('stop* 930 ');
        self::assertSame('Error: Stop time cannot be before start time!', rtrim($output->fetch()));
    }

    public function testUpdateStopWithTaskName(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('1000', '1100', 'old task title')
        ]);
        $output = $this->runCommand('stop* 1030 "new task title"');
        self::assertSame("Timer stop updated to 10:30 for 'new task title'", rtrim($output->fetch()));
    }

    public function testStopInInteractiveMode(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900')
        ]);

        $prompterMock = $this->getMockBuilder(Prompter::class)->disableOriginalConstructor()->getMock();
        $prompterMock->method('prompt')
            ->willReturnCallback(static function(string $promptText) {
                return rtrim($promptText, '> ');
            });
        DIContainer::$container->setParameter('prompter', $prompterMock);

        $output = $this->runCommandInInteractiveMode('stop 930');
        self::assertSame("Timer stopped at 09:30 for 'task'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:00', '9:30', 'task', 'comment');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStopWithTaskInInteractiveMode(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'test task')
        ]);

        $prompterMock = $this->getMockBuilder(Prompter::class)->disableOriginalConstructor()->getMock();
        $prompterMock->method('prompt')
            ->willReturnCallback(static function(string $promptText) {
                return rtrim($promptText, '> ');
            });
        DIContainer::$container->setParameter('prompter', $prompterMock);

        $output = $this->runCommandInInteractiveMode('stop 930 task123');
        self::assertSame("Timer stopped at 09:30 for 'task123'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:00', '9:30', 'task123', 'comment');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testUpdateStopInInteractiveMode(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '915')
        ]);

        $prompterMock = $this->getMockBuilder(Prompter::class)->disableOriginalConstructor()->getMock();
        $prompterMock->method('prompt')
            ->willReturnCallback(static function(string $promptText) {
                return rtrim($promptText, '> ');
            });
        DIContainer::$container->setParameter('prompter', $prompterMock);

        $output = $this->runCommandInInteractiveMode('stop* 930');
        self::assertSame("Timer stop updated to 09:30 for 'task'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:00', '9:30', 'task', 'comment');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testUpdateStopWithTaskSetInInteractiveMode(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '9:15', 'test task')
        ]);

        $prompterMock = $this->getMockBuilder(Prompter::class)->disableOriginalConstructor()->getMock();
        $prompterMock->method('prompt')
            ->willReturnCallback(static function(string $promptText) {
                return rtrim($promptText, '> ');
            });
        DIContainer::$container->setParameter('prompter', $prompterMock);

        $output = $this->runCommandInInteractiveMode('stop* 930');
        self::assertSame("Timer stop updated to 09:30 for 'test task'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:00', '9:30', 'test task', 'comment');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testUpdateStopWithTaskInInteractiveMode(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '915', 'test task')
        ]);

        $prompterMock = $this->getMockBuilder(Prompter::class)->disableOriginalConstructor()->getMock();
        $prompterMock->method('prompt')
            ->willReturnCallback(static function(string $promptText) {
                return rtrim($promptText, '> ');
            });
        DIContainer::$container->setParameter('prompter', $prompterMock);

        $output = $this->runCommandInInteractiveMode('stop* 930 task123');
        self::assertSame("Timer stop updated to 09:30 for 'task123'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:00', '9:30', 'task123', 'comment');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }
}
