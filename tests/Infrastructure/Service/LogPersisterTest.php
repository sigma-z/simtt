<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   19.12.19
 */

namespace Test\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Simtt\Domain\Model\Time;
use Simtt\Infrastructure\Service\LogPersister;
use Test\Helper\LogEntryCreator;
use Test\Helper\VirtualFileSystem;

class LogPersisterTest extends TestCase
{

    const LOG_DIR = 'vfs://logs';

    protected function setUp(): void
    {
        parent::setUp();
        VirtualFileSystem::setUpFileSystem();
        mkdir(self::LOG_DIR);
    }

    protected function tearDown(): void
    {
        VirtualFileSystem::tearDownFileSystem();
        parent::tearDown();
    }

    public function testSaveToEmptyLog(): void
    {
        $logEntry = LogEntryCreator::create('800', '830', 'task #1', 'comment #1');

        $persister = new LogPersister(self::LOG_DIR);
        $persister->saveLog($logEntry);

        $file = $persister->getFile();

        self::assertTrue($logEntry->isPersisted());
        self::assertFileExists($file);
        self::assertStringEqualsFile($file, $logEntry . "\n");
    }

    public function testSaveToExistingLog(): void
    {
        $persister = new LogPersister(self::LOG_DIR);

        $lastLogEntry = LogEntryCreator::create('900', '930', 'task #1', 'comment #1');
        $persister->saveLog($lastLogEntry);

        $currentLogEntry = LogEntryCreator::create('930', '1030', 'task #2', 'comment #2');
        $persister->saveLog($currentLogEntry);

        self::assertTrue($lastLogEntry->isPersisted());
        self::assertTrue($currentLogEntry->isPersisted());
        self::assertStringEqualsFile($persister->getFile(), $lastLogEntry . "\n" . $currentLogEntry . "\n");
    }

    public function testUpdateStopTimeForLogEntry(): void
    {
        $persister = new LogPersister(self::LOG_DIR);

        $lastLogEntry = LogEntryCreator::create('900', '930', 'task #1', 'comment #1');
        $persister->saveLog($lastLogEntry);

        $currentLogEntry = LogEntryCreator::create('930', '1030', 'task #2', 'comment #2');
        $persister->saveLog($currentLogEntry);

        $currentLogEntry->stopTime = new Time('11:15');
        $persister->saveLog($currentLogEntry);

        self::assertStringEqualsFile($persister->getFile(), $lastLogEntry . "\n" . $currentLogEntry . "\n");
    }

    public function testUpdateStartTimeForLogEntry(): void
    {
        $persister = new LogPersister(self::LOG_DIR);

        $lastLogEntry = LogEntryCreator::create('900', '930', 'task #1', 'comment #1');
        $persister->saveLog($lastLogEntry);

        $currentLogEntry = LogEntryCreator::create('930', '1030', 'task #2', 'comment #2');
        $persister->saveLog($currentLogEntry);

        $currentLogEntry->startTime = new Time('9:45');
        $persister->saveLog($currentLogEntry);
        $currentLogEntry->startTime = new Time('9:50');
        $persister->saveLog($currentLogEntry);

        self::assertStringEqualsFile($persister->getFile(), $lastLogEntry . "\n" . $currentLogEntry . "\n");
    }

}
