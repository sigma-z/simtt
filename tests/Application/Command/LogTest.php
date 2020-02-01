<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   15.01.20
 */

namespace Test\Application\Command;

use Helper\DIContainer;
use Test\Helper\LogEntryCreator;
use Test\Helper\TableRowsCellParser;
use Test\Helper\VirtualFileSystem;

class LogTest extends TestCase
{

    /** @var int */
    private $backupShowLogItems;

    protected function getCommandShortName(): string
    {
        return 'log.cmd';
    }

    protected function setUp(): void
    {
        parent::setUp();
        VirtualFileSystem::setUpFileSystem();
        $this->backupShowLogItems = DIContainer::$container->getParameter('config.showLogItems');
    }

    protected function tearDown(): void
    {
        DIContainer::$container->setParameter('config.showLogItems', $this->backupShowLogItems);
        VirtualFileSystem::tearDownFileSystem();
        parent::tearDown();
    }

    public function testLogEmpty(): void
    {
        $output = $this->runCommand('log');
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
            ['09:00', '10:00', '01:00', 'task #1', ''],
            ['10:00', '10:50', '00:50', 'task #2', ''],
        ];

        DIContainer::$container->setParameter('config.showLogItems', 2);
        DIContainer::$container->reset();
        $output = $this->runCommand('log');
        $content = $output->fetch();
        $rowsData = TableRowsCellParser::parse($content, 5);
        self::assertSame($expectedRowsData, $rowsData);
    }

    public function testLog(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '', 'task #2'),
            LogEntryCreator::createToString('1050', '', 'task #1', 'comment'),
            LogEntryCreator::createToString('1130', '', 'task #3'),
        ]);
        $expectedRowsData = [
            ['09:00', '10:00', '01:00', 'task #1', ''],
            ['10:00', '10:50', '00:50', 'task #2', ''],
            ['10:50', '11:30', '00:40', 'task #1', 'comment'],
            ['11:30', '', 'running ...', 'task #3', ''],
        ];

        $output = $this->runCommand('log');
        $content = $output->fetch();
        $rowsData = $this->parseRowsCellData($content);
        self::assertSame($expectedRowsData, $rowsData);
    }

    public function testLogAcrossDays(): void
    {
        LogEntryCreator::setUpLogFileYesterday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '11:50', 'task #2'),
        ]);
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '', 'task #2'),
            LogEntryCreator::createToString('1050', '', 'task #1', 'comment'),
            LogEntryCreator::createToString('1130', '12:00', 'task #3'),
        ]);
        $expectedRowsData = [
            ['09:00', '10:00', '01:00', 'task #1', ''],
            ['10:00', '10:50', '00:50', 'task #2', ''],
            ['10:50', '11:30', '00:40', 'task #1', 'comment'],
            ['11:30', '12:00', '00:30', 'task #3', ''],
            ['09:00', '10:00', '01:00', 'task #1', ''],
            ['10:00', '11:50', '01:50', 'task #2', ''],
        ];

        $output = $this->runCommand('log');
        $content = $output->fetch();
        $rowsData = $this->parseRowsCellData($content);
        self::assertSame($expectedRowsData, $rowsData);
    }

    public function testLogAcrossDaysWithRange(): void
    {
        LogEntryCreator::setUpLogFileYesterday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '11:50', 'task #2'),
        ]);
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '', 'task #2'),
            LogEntryCreator::createToString('1050', '', 'task #1', 'comment'),
            LogEntryCreator::createToString('1130', '12:00', 'task #3'),
        ]);
        $expectedRowsData = [
            ['10:00', '10:50', '00:50', 'task #2', ''],
            ['10:50', '11:30', '00:40', 'task #1', 'comment'],
            ['11:30', '12:00', '00:30', 'task #3', ''],
            ['09:00', '10:00', '01:00', 'task #1', ''],
        ];

        $output = $this->runCommand('log 2-5');
        $content = $output->fetch();
        $rowsData = $this->parseRowsCellData($content);
        self::assertSame($expectedRowsData, $rowsData);
    }

    private function parseRowsCellData(string $content): array
    {
        return TableRowsCellParser::parse($content, 5);
    }

}
