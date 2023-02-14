<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   02.01.20
 */

namespace Test\Application\Command;

use PHPUnit\Framework\Attributes\DataProvider;
use Test\Helper\LogEntryCreator;
use Test\Helper\VirtualFileSystemTrait;

class StatusTest extends TestCase
{
    use VirtualFileSystemTrait;

    protected function getCommandShortName(): string
    {
        return 'status.cmd';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpFileSystem();
    }

    #[DataProvider('provideStatusTimerRunning')]
    public function testStatusTimerRunning(string $expectedContent, string $task, string $comment): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::create('9:30', '', $task, $comment)
        ]);
        $output = $this->runCommand('status');
        $content = $output->fetch();
        $content = preg_replace('/running for \d+:\d+/', 'running for 00:00', $content);
        self::assertSame($expectedContent, $content);
    }

    public static function provideStatusTimerRunning(): array
    {
        return [
            [
                'expectedContent' => "STATUS: Timer started at 09:30 (running for 00:00)" . PHP_EOL
                    . "Task: task" . PHP_EOL
                    . "Comment: comment" . PHP_EOL,
                'task' => 'task',
                'comment' => 'comment'
            ],
            [
                'expectedContent' => "STATUS: Timer started at 09:30 (running for 00:00)" . PHP_EOL
                    . "Task: -" . PHP_EOL
                    . "Comment: comment" . PHP_EOL,
                'task' => '',
                'comment' => 'comment'
            ],
            [
                'expectedContent' => "STATUS: Timer started at 09:30 (running for 00:00)" . PHP_EOL
                    . "Task: task" . PHP_EOL,
                'task' => 'task',
                'comment' => ''
            ],
            [
                'expectedContent' => "STATUS: Timer started at 09:30 (running for 00:00)" . PHP_EOL
                    . "Task: -" . PHP_EOL,
                'task' => '',
                'comment' => ''
            ],
        ];
    }

    public function testStatusStoppedTimer(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::create('9:30', '10:00', 'task', 'comment')
        ]);
        $output = $this->runCommand('status');
        $content = $output->fetch();
        $expectedContent = "STATUS: Last timer ran from 09:30 - 10:00 (=0:30)" . PHP_EOL
                    . "Task: task" . PHP_EOL
                    . "Comment: comment" . PHP_EOL;
        self::assertSame($expectedContent, $content);
    }

    public function testStatusYesterdayLog(): void
    {
        LogEntryCreator::setUpLogFileYesterday([
            LogEntryCreator::create('9:30', '', 'task', 'comment')
        ]);

        $output = $this->runCommand('status');
        $content = $output->fetch();
        self::assertSame('STATUS: No timer is running.', rtrim($content));
    }

    public function testStatusEmptyLog(): void
    {
        $output = $this->runCommand('status');
        $content = $output->fetch();
        self::assertSame('STATUS: No timer is running.', rtrim($content));
    }

}
