<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   22.12.19
 */

namespace Test\Application\Command;

use Helper\DIContainer;
use PHPUnit\Framework\TestCase;
use Simtt\Infrastructure\Service\LogFile;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Test\Helper\LogEntryCreator;
use Test\Helper\VirtualFileSystem;

class StartTest extends TestCase
{

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
        $this->runCommand('start 930');
        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartUpdateStart(): void
    {
        LogEntryCreator::setUpLogFile((new \DateTime())->format('Y-m-d'), [
            LogEntryCreator::createToString('900')
        ]);
        $this->runCommand('start 930');

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartWithTaskTitle(): void
    {
        $this->runCommand('start 930 task');

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30', '', 'task');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartUpdateWithTaskTitleWillNotBeOverwritten(): void
    {
        $expectedTaskTitle = 'test task';
        LogEntryCreator::setUpLogFile((new \DateTime())->format('Y-m-d'), [
            LogEntryCreator::createToString('900', '', $expectedTaskTitle)
        ]);
        $this->runCommand('start 930 task');

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntry = LogEntryCreator::create('9:30', '', $expectedTaskTitle);
        self::assertStringEqualsFile($logFile->getFile(),  $logEntry . "\n");
    }

    public function testStartAddsEntry(): void
    {
        $logEntryOne = LogEntryCreator::createToString('900', '1000');
        LogEntryCreator::setUpLogFile((new \DateTime())->format('Y-m-d'), [
            $logEntryOne
        ]);
        $this->runCommand('start 10:30');

        $logFile = LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR);
        $logEntryTwo = LogEntryCreator::create('10:30');
        self::assertStringEqualsFile($logFile->getFile(),  $logEntryOne . "\n" . $logEntryTwo . "\n");
    }

    protected function runCommand(string $stringInput): void
    {
        $application = new Application('Simtt');
        $application->setAutoExit(false);
        /** @noinspection PhpParamsInspection */
        $application->add(DIContainer::$container->get('start.cmd'));
        $input = new StringInput($stringInput);
        $application->run($input);
    }
}
