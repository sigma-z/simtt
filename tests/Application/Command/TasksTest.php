<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   24.02.20
 */

namespace Test\Application\Command;

use Helper\DIContainer;
use Test\Helper\LogEntryCreator;
use Test\Helper\TableRowsCellParser;
use Test\Helper\VirtualFileSystem;

class TasksTest extends TestCase
{

    /** @var int */
    private $backupShowTaskItems;

    protected function getCommandShortName(): string
    {
        return 'tasks.cmd';
    }

    protected function setUp(): void
    {
        parent::setUp();
        VirtualFileSystem::setUpFileSystem();
        $this->backupShowTaskItems = DIContainer::$container->getParameter('config.showTaskItems');
    }

    protected function tearDown(): void
    {
        DIContainer::$container->setParameter('config.showTaskItems', $this->backupShowTaskItems);
        VirtualFileSystem::tearDownFileSystem();
        parent::tearDown();
    }

    public function testLogEmpty(): void
    {
        $output = $this->runCommand('tasks');
        self::assertSame('No entries found', rtrim($output->fetch()));
    }

    public function testLogLimited(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '', 'task #2'),
            LogEntryCreator::createToString('1050', '', 'task #1', 'comment'),
            LogEntryCreator::createToString('1130', '', 'task #3'),
        ]);
        $expectedRowsData = [
            ['#1', 'task #1', '2'],
            ['#2', 'task #2', '1'],
        ];

        DIContainer::$container->setParameter('config.showTaskItems', 2);
        DIContainer::$container->reset();
        $output = $this->runCommand('tasks');
        $content = $output->fetch();
        $rowsData = TableRowsCellParser::parse($content, 3);
        self::assertSame($expectedRowsData, $rowsData);
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
        $expectedRowsData = [
            ['#1', 'task #5', '3'],
            ['#2', 'task #1', '2'],
            ['#3', 'task #3', '1'],
            ['#4', 'task #4', '1'],
        ];
        DIContainer::$container->setParameter('config.showTaskItems', 4);
        DIContainer::$container->reset();
        $output = $this->runCommand('tasks');
        $content = $output->fetch();
        $rowsData = TableRowsCellParser::parse($content, 3);
        self::assertSame($expectedRowsData, $rowsData);
    }
}
