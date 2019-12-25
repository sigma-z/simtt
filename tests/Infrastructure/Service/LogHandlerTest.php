<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   24.12.19
 */

namespace Infrastructure\Service;

use PHPUnit\Framework\TestCase;
use Simtt\Infrastructure\Service\LogFileFinder;
use Simtt\Infrastructure\Service\LogHandler;
use Test\Helper\VirtualFileSystem;

class LogHandlerTest extends TestCase
{

    /** @var LogHandler */
    private $logHandler;

    protected function setUp(): void
    {
        parent::setUp();
        VirtualFileSystem::setUpFileSystem();
        $this->logHandler = new LogHandler(new LogFileFinder(VirtualFileSystem::LOG_DIR));
    }

    protected function tearDown(): void
    {
        VirtualFileSystem::tearDownFileSystem();
        parent::tearDown();
    }

    public function testGetAllLogs(): void
    {
        $this->setUpLogEntries('2019-11-30', [
            '12:00;     ;"";""',
            '12:30;     ;"";""'
        ]);
        $this->setUpLogEntries('2019-12-24', [
            '22:20;     ;"";""',
            '22:25;     ;"";""'
        ]);
        $this->setUpLogEntries('2019-12-25', [
            '12:20;     ;"";""',
            '12:30;     ;"";""'
        ]);

        $logEntries = $this->logHandler->getAllLogs();
        self::assertCount(6, $logEntries);
    }

    public function testGetLogOnEmptyLogReturnsNull(): void
    {
        $currentLog = $this->logHandler->getCurrentLog();
        $lastLog = $this->logHandler->getLastLog();
        self::assertNull($currentLog);
        self::assertNull($lastLog);
    }

    public function testGetLogForYesterdayReturnsNull(): void
    {
        $yesterday = (new \DateTime())->sub(new \DateInterval('P1D'))->format('Y-m-d');
        $this->setUpLogEntries($yesterday, [
            '12:00;     ;"";""',
            '12:30;     ;"";""'
        ]);
        $currentLog = $this->logHandler->getCurrentLog();
        $lastLog = $this->logHandler->getLastLog();
        self::assertNull($currentLog);
        self::assertNull($lastLog);
    }

    public function testGetLogForStartedTimerReturnsNull(): void
    {
        $this->setUpLogEntries((new \DateTime())->format('Y-m-d'), [
            '12:00;     ;"";""'
        ]);
        $currentLog = $this->logHandler->getCurrentLog();
        $lastLog = $this->logHandler->getLastLog();
        self::assertNull($lastLog);
        self::assertNotNull($currentLog);
        self::assertSame('12:00', (string)$currentLog->startTime);
    }

    public function testGetLogForStoppedTimerReturnsObject(): void
    {
        $this->setUpLogEntries((new \DateTime())->format('Y-m-d'), [
            '12:00;12:30;"";""'
        ]);
        $currentLog = $this->logHandler->getCurrentLog();
        $lastLog = $this->logHandler->getLastLog();
        self::assertNull($currentLog);
        self::assertNotNull($lastLog);
        self::assertSame('12:30', (string)$lastLog->stopTime);
    }

    private function setUpLogEntries(string $date, array $entries): void
    {
        [$year, $month, ] = explode('-', $date);
        $dir = VirtualFileSystem::LOG_DIR . "/$year/$month";
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents("$dir/$date.log", implode("\n", $entries) . "\n");
    }
}
