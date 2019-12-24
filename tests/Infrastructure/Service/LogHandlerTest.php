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

        $logHandler = new LogHandler(new LogFileFinder(VirtualFileSystem::LOG_DIR));
        $logEntries = $logHandler->getAllLogs();
        self::assertCount(6, $logEntries);
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
