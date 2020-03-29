<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   21.01.20
 */

namespace Test\Application\Command;

use Helper\DIContainer;
use Simtt\Infrastructure\Service\Clock\FixedClock;
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
        DIContainer::$container->set('clock', new FixedClock(new \DateTime('12:00:00')));
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
        $rowsData = TableRowsCellParser::parse($content, 5);
        self::assertSame([
            ['09:00', '10:00', '01:00', 'task #1', ''],
            ['10:00', '10:50', '00:50', 'task #2', ''],
            ['10:50', '11:30', '00:40', 'task #1', 'comment'],
            ['11:30', '', '00:30 (running)', 'task #3', ''],
        ], $rowsData);
    }

    public function testDayLogWithStopTime(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '', 'task #2'),
            LogEntryCreator::createToString('1050', '', 'task #1', 'comment'),
            LogEntryCreator::createToString('1130', '1300', 'task #3'),
        ]);

        $output = $this->runCommand('day');
        $content = $output->fetch();
        $rowsData = TableRowsCellParser::parse($content, 5);
        self::assertSame([
            ['09:00', '10:00', '01:00', 'task #1', ''],
            ['10:00', '10:50', '00:50', 'task #2', ''],
            ['10:50', '11:30', '00:40', 'task #1', 'comment'],
            ['11:30', '13:00', '01:30', 'task #3', ''],
        ], $rowsData);
    }

    public function testDayLogWithGaps(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '1030', 'task #2'),
            LogEntryCreator::createToString('1050', '', 'task #1', 'comment'),
            LogEntryCreator::createToString('1130', '1200', 'task #3'),
            LogEntryCreator::createToString('1230', '1300', 'task #3'),
        ]);

        $output = $this->runCommand('day');
        $content = $output->fetch();
        $rowsData = TableRowsCellParser::parse($content, 5);
        self::assertSame([
            ['09:00', '10:00', '01:00', 'task #1', ''],
            ['10:00', '10:30', '00:30', 'task #2', ''],
            ['10:30', '10:50', '00:20', '-- no time logged --', ''],
            ['10:50', '11:30', '00:40', 'task #1', 'comment'],
            ['11:30', '12:00', '00:30', 'task #3', ''],
            ['12:00', '12:30', '00:30', '-- no time logged --', ''],
            ['12:30', '13:00', '00:30', 'task #3', ''],
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
        $rowsData = TableRowsCellParser::parse($content, 4, true);
        self::assertSame([
            ['01:40', '2', 'task #1', 'comment'],
            ['00:50', '1', 'task #2', ''],
            ['00:30 (running)', '1', 'task #3', ''],
        ], $rowsData);

        $sumData = TableRowsCellParser::parseSumRow($content);
        self::assertSame(['03:00 (running)', '4', 'Total time', 'Logged from 09:00 to 12:00 (running)'], $sumData);
    }

    public function testDaySumWithStopTime(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '', 'task #2'),
            LogEntryCreator::createToString('1050', '', 'task #1', 'comment'),
            LogEntryCreator::createToString('1130', '1300', 'task #3'),
        ]);

        $output = $this->runCommand('day sum');
        $content = $output->fetch();
        $rowsData = TableRowsCellParser::parse($content, 4, true);
        self::assertSame([
            ['01:40', '2', 'task #1', 'comment'],
            ['01:30', '1', 'task #3', ''],
            ['00:50', '1', 'task #2', ''],
        ], $rowsData);

        $sumData = TableRowsCellParser::parseSumRow($content);
        self::assertSame(['04:00', '4', 'Total time', 'Logged from 09:00 to 13:00'], $sumData);
    }

    public function testDaySumWithGaps(): void
    {
        LogEntryCreator::setUpLogFileToday([
            LogEntryCreator::createToString('900', '', 'task #1'),
            LogEntryCreator::createToString('1000', '1030', 'task #2'),
            LogEntryCreator::createToString('1050', '', 'task #1', 'comment'),
            LogEntryCreator::createToString('1130', '1200', 'task #3'),
            LogEntryCreator::createToString('1230', '1300', 'task #3'),
        ]);

        $output = $this->runCommand('day sum');
        $content = $output->fetch();
        $rowsData = TableRowsCellParser::parse($content, 4, true);
        self::assertSame([
            ['01:40', '2', 'task #1', 'comment'],
            ['01:00', '2', 'task #3', ''],
            ['00:50', '2', '-- no time logged --', ''],
            ['00:30', '1', 'task #2', ''],
        ], $rowsData);

        $sumData = TableRowsCellParser::parseSumRow($content);
        self::assertSame(['03:10', '5', 'Total time', 'Logged from 09:00 to 13:00'], $sumData);
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
        $rowsData = TableRowsCellParser::parse($content, 5);
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
        $rowsData = TableRowsCellParser::parse($content, 4, true);
        self::assertSame([
            ['01:10 (running)', '2', 'task #1', ''],
            ['00:35', '1', 'task #2', ''],
        ], $rowsData);

        $sumData = TableRowsCellParser::parseSumRow($content);
        self::assertSame(['01:45 (running)', '3', 'Total time', 'Logged from 10:00 to ?'], $sumData);
    }
}
