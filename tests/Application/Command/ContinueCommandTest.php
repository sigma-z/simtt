<?php
declare(strict_types=1);

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */

namespace Test\Application\Command;

use Helper\DIContainer;
use Simtt\Infrastructure\Service\Clock\FixedClock;
use Simtt\Infrastructure\Service\LogFile;
use Test\Helper\LogEntryCreator;
use Test\Helper\VirtualFileSystemTrait;

class ContinueCommandTest extends TestCase
{
    use VirtualFileSystemTrait;

    protected function getCommandShortName(): string
    {
        return 'continue.cmd';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFileSystem();
        DIContainer::$container->set('clock', new FixedClock(new \DateTime('12:00:00')));
    }

    public function testEmptyLog(): void
    {
        $output = $this->runCommand('continue');
        self::assertSame('No stopped timer found', rtrim($output->fetch()));
    }

    public function testNoStoppedTimer(): void
    {
        $output = $this->runCommand('continue');
        self::assertSame('No stopped timer found', rtrim($output->fetch()));
    }

    public function testContinueNow(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '1100', 'task #2'),
        ]);
        $output = $this->runCommand('continue');
        self::assertSame("Timer continued on 12:00 for 'task #2'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntries = [
            LogEntryCreator::create('9:00', '', 'task #1'),
            LogEntryCreator::create('10:00', '11:00', 'task #2'),
            LogEntryCreator::create('12:00', '', 'task #2'),
        ];
        self::assertStringEqualsFile($logFile->getFile(),  implode("\n", $logEntries) . "\n");
    }

    public function testContinueWithStartTime(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '1100', 'task #2'),
        ]);
        $output = $this->runCommand('continue 12:00');
        self::assertSame("Timer continued on 12:00 for 'task #2'", rtrim($output->fetch()));

        $logFile = LogFile::createTodayLogFile(LOG_DIR);
        $logEntries = [
            LogEntryCreator::create('9:00', '', 'task #1'),
            LogEntryCreator::create('10:00', '11:00', 'task #2'),
            LogEntryCreator::create('12:00', '', 'task #2'),
        ];
        self::assertStringEqualsFile($logFile->getFile(),  implode("\n", $logEntries) . "\n");
    }

    public function testContinueBeforeStoppedTask(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '1100', 'task #2'),
        ]);

        $output = $this->runCommand('continue 10:50');
        self::assertSame('Error: Stop time of last log is newer than the new start time.', rtrim($output->fetch()));
    }
}
