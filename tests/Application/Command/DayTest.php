<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   21.01.20
 */

namespace Test\Application\Command;

use Test\Helper\LogEntryCreator;
use Test\Helper\TableRowsCellParser;
use Test\Helper\VirtualFileSystem;

class DayTest extends TestCase
{

    protected function getCommandShortName(): string
    {
        return 'day.cmd';
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

    public function testDayOnEmptyLog(): void
    {
        $output = $this->runCommand('day');
        self::assertSame('No entries found for today', rtrim($output->fetch()));
    }

    public function testDayLog(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '', 'task #2'),
            LogEntryCreator::createToString('1050', '', 'task #1', 'comment'),
            LogEntryCreator::createToString('1130', '', 'task #3'),
        ]);

        $output = $this->runCommand('day');
        $content = $output->fetch();
        $rowsData = TableRowsCellParser::parse($content);
        self::assertSame([
            ['09:00', '10:00', '01:00', 'task #1', ''],
            ['10:00', '10:50', '00:50', 'task #2', ''],
            ['10:50', '11:30', '00:40', 'task #1', 'comment'],
            ['11:30', '', 'running ...', 'task #3', ''],
        ], $rowsData);
    }

    public function testDaySum(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '', 'task #2'),
            LogEntryCreator::createToString('1050', '', 'task #1', 'comment'),
            LogEntryCreator::createToString('1130', '', 'task #3'),
        ]);

        $output = $this->runCommand('day sum');
        $content = $output->fetch();
        $rowsData = TableRowsCellParser::parse($content, true);
        self::assertSame([
            ['01:40', '2', 'task #1', 'comment'],
            ['00:50', '1', 'task #2', ''],
            ['running ...', '1', 'task #3', ''],
        ], $rowsData);

        $sumData = TableRowsCellParser::parseSumRow($content);
        self::assertSame(['02:30', '4', 'Total time', ''], $sumData);
    }

    public function testYesterdayOnEmptyLog(): void
    {
        $output = $this->runCommand('day 1');
        self::assertSame('No entries found for yesterday', rtrim($output->fetch()));
    }


    public function testYesterdayLog(): void
    {
        LogEntryCreator::setUpLogFileYesterday([
            LogEntryCreator::createToString('1000', '', 'task #1'),
            LogEntryCreator::createToString('1110', '', 'task #2'),
            LogEntryCreator::createToString('1145', '', 'task #1'),
        ]);
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '', 'task #2'),
            LogEntryCreator::createToString('1050', '', 'task #1', 'comment'),
            LogEntryCreator::createToString('1130', '', 'task #3'),
        ]);

        $output = $this->runCommand('day 1');
        $content = $output->fetch();
        $rowsData = TableRowsCellParser::parse($content);
        self::assertSame([
            ['10:00', '11:10', '01:10', 'task #1', ''],
            ['11:10', '11:45', '00:35', 'task #2', ''],
            ['11:45', '', 'running ...', 'task #1', ''],
        ], $rowsData);
    }

    public function testYesterdaySum(): void
    {
        LogEntryCreator::setUpLogFileYesterday([
            LogEntryCreator::createToString('1000', '', 'task #1'),
            LogEntryCreator::createToString('1110', '', 'task #2'),
            LogEntryCreator::createToString('1145', '', 'task #1'),
        ]);
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '', 'task #2'),
            LogEntryCreator::createToString('1050', '', 'task #1', 'comment'),
            LogEntryCreator::createToString('1130', '', 'task #3'),
        ]);

        $output = $this->runCommand('day 1 sum');
        $content = $output->fetch();
        $rowsData = TableRowsCellParser::parse($content, true);
        self::assertSame([
            ['00:35', '1', 'task #2', ''],
            ['running ...', '2', 'task #1', ''],
        ], $rowsData);

        $sumData = TableRowsCellParser::parseSumRow($content);
        self::assertSame(['01:45', '3', 'Total time', ''], $sumData);
    }
}