<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   19.12.19
 */

namespace Test\Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Simtt\Domain\Model\LogEntry;
use Simtt\Domain\Model\Time;
use Simtt\Infrastructure\Service\LogPersister;
use Test\Helper\VirtualFileSystemTrait;

class LogPersisterTest extends TestCase
{

    use VirtualFileSystemTrait;

    const LOG_DIR = 'vfs://log';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        mkdir(self::LOG_DIR);
    }

    public function testSaveLog(): void
    {
        $logEntry = new LogEntry();
        $logEntry->startTime = new Time('900');
        $logEntry->stopTime = new Time('930');
        $logEntry->task = 'task';
        $logEntry->comment = 'comment';

        $persister = new LogPersister(self::LOG_DIR);
        $persister->saveLog($logEntry);
        $file = $persister->getFile();

        self::assertFileExists($file);
        self::assertStringEqualsFile($file, (string)$logEntry);
    }
}
