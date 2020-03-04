<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   03.03.20
 */

namespace Test\Infrastructure\Service;

use Helper\DIContainer;
use PHPUnit\Framework\TestCase;
use Simtt\Domain\Model\RecentTask;
use Simtt\Infrastructure\Service\RecentTaskList;
use Test\Helper\LogEntryCreator;
use Test\Helper\VirtualFileSystem;

class RecentTaskListTest extends TestCase
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

    public function testEmpty(): void
    {
        $recentTaskList = new RecentTaskList(DIContainer::$container->get('logFileFinder'), 2);
        $recentTasks = $recentTaskList->getTasks();
        self::assertCount(0, $recentTasks);
    }

    public function testLogLimited(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '', 'task #2'),
            LogEntryCreator::createToString('1050', '', 'task #1', 'comment'),
            LogEntryCreator::createToString('1130', '', 'task #3'),
        ]);

        $recentTaskList = new RecentTaskList(DIContainer::$container->get('logFileFinder'), 2);
        $recentTasks = $recentTaskList->getTasks();

        self::assertCount(2, $recentTasks);
        self::assertRecentTask('task #1', 2, $recentTasks[0]);
        self::assertRecentTask('task #2', 1, $recentTasks[1]);
    }

    public function testLogAcrossDays(): void
    {
        LogEntryCreator::setUpLogFileYesterday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '11:50', 'task #2'),
            LogEntryCreator::createToString('1200', '', 'task #1'),
        ]);
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #5'),
            LogEntryCreator::createToString('1050', '', 'task #5', 'comment'),
            LogEntryCreator::createToString('1130', '12:00', 'task #3'),
            LogEntryCreator::createToString('1330', '', 'task #4'),
            LogEntryCreator::createToString('1500', '', 'task #5'),
            LogEntryCreator::createToString('1630', '', ''),
        ]);

        $recentTaskList = new RecentTaskList(DIContainer::$container->get('logFileFinder'), 4);
        $recentTasks = $recentTaskList->getTasks();

        self::assertCount(4, $recentTasks);
        self::assertRecentTask('task #5', 3, $recentTasks[0]);
        self::assertRecentTask('task #1', 2, $recentTasks[1]);
        self::assertRecentTask('task #3', 1, $recentTasks[2]);
        self::assertRecentTask('task #4', 1, $recentTasks[3]);
    }

    private static function assertRecentTask(string $task, int $count, RecentTask $recentTask): void
    {
        self::assertSame($task, $recentTask->getTask());
        self::assertSame($count, $recentTask->getCount());
    }
}
