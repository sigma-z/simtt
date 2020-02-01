<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   21.01.20
 */

namespace Test\Application\Command\Helper;

use PHPUnit\Framework\TestCase;
use Simtt\Application\Command\Helper\LogAggregationTable;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;
use Test\Helper\LogEntryCreator;
use Test\Helper\TableRowsCellParser;

class LogAggregationTableTest extends TestCase
{

    public function testEmptyTable(): void
    {
        $output = new BufferedOutput();
        $table = new LogAggregationTable(new Table($output));
        $table->render();

        $rowsData = TableRowsCellParser::parse($output->fetch(), 5);
        self::assertEmpty($rowsData);
    }

    public function testOneEntry(): void
    {
        $output = new BufferedOutput();
        $table = new LogAggregationTable(new Table($output));
        $table->processLogEntries([LogEntryCreator::create('9:00', '10:00', 'task #1')]);
        $table->render();

        $expectedRowsData = [
            ['01:00', '1', 'task #1', '']
        ];
        $content = $output->fetch();

        $rowsData = TableRowsCellParser::parse($content, 4, true);
        self::assertSame($expectedRowsData, $rowsData);

        $sumData = TableRowsCellParser::parseSumRow($content);
        self::assertSame(['01:00', '1', 'Total time', ''], $sumData);
    }

    public function testOneEntryWithNoDuration(): void
    {
        $output = new BufferedOutput();
        $table = new LogAggregationTable(new Table($output));
        $table->processLogEntries([LogEntryCreator::create('9:00', '9:00', 'task #1')]);
        $table->render();

        $expectedRowsData = [
            ['00:00', '1', 'task #1', '']
        ];
        $content = $output->fetch();

        $rowsData = TableRowsCellParser::parse($content, 4, true);
        self::assertSame($expectedRowsData, $rowsData);

        $sumData = TableRowsCellParser::parseSumRow($content);
        self::assertSame(['00:00', '1', 'Total time', ''], $sumData);
    }

    public function testAggregationWithTaskRunning(): void
    {
        $output = new BufferedOutput();
        $table = new LogAggregationTable(new Table($output));
        $table->processLogEntries([
            LogEntryCreator::create('9:00', '', 'task #1', 'comment #1'),
            LogEntryCreator::create('10:00', '', 'task #1', 'comment #2'),
        ]);
        $table->render();

        $expectedRowsData = [
            ['running ...', '2', 'task #1', 'comment #1'],
            ['', '', '', 'comment #2'],
        ];
        $content = $output->fetch();

        $rowsData = TableRowsCellParser::parse($content, 4, true);
        self::assertSame($expectedRowsData, $rowsData);

        $sumData = TableRowsCellParser::parseSumRow($content);
        self::assertSame(['01:00', '2', 'Total time', ''], $sumData);
    }

    public function testAggregation(): void
    {
        $output = new BufferedOutput();
        $table = new LogAggregationTable(new Table($output));
        $table->processLogEntries([
            LogEntryCreator::create('9:00', '', 'Task #1', 'comment #1'),
            LogEntryCreator::create('10:00', '', 'task #2', 'comment #2'),
            LogEntryCreator::create('12:30', '13:00', 'task #1', 'comment #3')
        ]);
        $table->render();

        $expectedRowsData = [
            ['02:30', '1', 'task #2', 'comment #2'],
            ['01:30', '2', 'task #1', 'comment #1'],
            ['', '', '', 'comment #3'],
        ];
        $content = $output->fetch();

        $rowsData = TableRowsCellParser::parse($content, 4, true);
        self::assertSame($expectedRowsData, $rowsData);

        $sumData = TableRowsCellParser::parseSumRow($content);
        self::assertSame(['04:00', '3', 'Total time', ''], $sumData);
    }
}
