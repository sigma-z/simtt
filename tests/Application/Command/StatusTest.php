<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   02.01.20
 */

namespace Test\Application\Command;

use Test\Helper\LogEntryCreator;
use Test\Helper\VirtualFileSystem;

class StatusTest extends TestCase
{

    protected function getCommandShortName(): string
    {
        return 'status.cmd';
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

    /**
     * @dataProvider provideStatusTimerRunning
     * @param string $task
     * @param string $comment
     * @param string $expectedContent
     */
    public function testStatusTimerRunning(string $expectedContent, string $task, string $comment): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::create('9:30', '', $task, $comment)
        ]);
        $output = $this->runCommand('status');
        $content = $output->fetch();
        self::assertSame($expectedContent, $content);
    }

    public function provideStatusTimerRunning(): array
    {
        return [
            [
                'expectedContent' => "STATUS: Timer started at 09:30\n"
                    . "Task: task\n"
                    . "Comment: comment\n",
                'task' => 'task',
                'comment' => 'comment'
            ],
            [
                'expectedContent' => "STATUS: Timer started at 09:30\n"
                    . "Task: -\n"
                    . "Comment: comment\n",
                'task' => '',
                'comment' => 'comment'
            ],
            [
                'expectedContent' => "STATUS: Timer started at 09:30\n"
                    . "Task: task\n",
                'task' => 'task',
                'comment' => ''
            ],
            [
                'expectedContent' => "STATUS: Timer started at 09:30\n"
                    . "Task: -\n",
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
        $expectedContent = "STATUS: Last timer ran from 09:30 - 10:00 (=0:30)\n"
                    . "Task: task\n"
                    . "Comment: comment\n";
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
