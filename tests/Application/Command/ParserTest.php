<?php
declare(strict_types=1);

namespace Test\Application\Command;

use PHPUnit\Framework\TestCase;
use Simtt\Application\Command\Parser;
use Simtt\Application\Command\ParseResult;
use Simtt\Application\Command\PatternProvider;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class ParserTest extends TestCase
{

    /**
     * @dataProvider provideParseStartStop
     * @param string $input
     * @param string $expectedCommand
     * @param array  $expectedArgs
     */
    public function testParseStartStop(string $input, string $expectedCommand, array $expectedArgs): void
    {
        $parseResult = self::executeParse($input);
        $this->assertParse($expectedCommand, $expectedArgs, $parseResult);
    }

    public function provideParseStartStop(): array
    {
        return [
            ['start*  2221  Task title sample', 'start*', ['2221', 'Task title sample']],
            ['start* 921', 'start*', ['921', null]],
            ['  start  ', 'start', [null, null]],
            ['stop 921', 'stop', ['921', null]],
            ['stop* 921', 'stop*', ['921', null]],
            ['start 921', 'start', ['921', null]],
            ['start 2221', 'start', ['2221', null]],
            ['start Task title sample', 'start', [null, 'Task title sample']],
            ['start 921 Task title sample', 'start', ['921', 'Task title sample']],
            ['start 9:21 Task title sample', 'start', ['9:21', 'Task title sample']],
            ['  start 09:21 Task title sample ', 'start', ['09:21', 'Task title sample']],
        ];
    }

    /**
     * @dataProvider provideParseLog
     * @param string $input
     * @param string $expectedCommand
     * @param array  $expectedArgs
     */
    public function testParseLog(string $input, string $expectedCommand, array $expectedArgs): void
    {
        $parseResult = self::executeParse($input);
        $this->assertParse($expectedCommand, $expectedArgs, $parseResult);
    }

    public function provideParseLog(): array
    {
        return [
            ['log', 'log', [null, null]],
            ['log 100', 'log', ['100', null]],
            ['log all', 'log', ['all', null]],
            ['log 100-100', 'log', ['100-100', null]],
            ['log 100-120', 'log', ['100-120', null]],
            ['log 100-120 desc', 'log', ['100-120', 'desc']],
            ['log 100-120 asc', 'log', ['100-120', 'asc']],
            ['log 100 asc', 'log', ['100', 'asc']],
        ];
    }

    /**
     * @dataProvider provideParseOtherTypes
     * @param string $input
     * @param string $expectedCommand
     * @param array  $expectedArgs
     */
    public function testParseOtherTypes(string $input, string $expectedCommand, array $expectedArgs): void
    {
        $parseResult = self::executeParse($input);
        $this->assertParse($expectedCommand, $expectedArgs, $parseResult);
    }

    public function provideParseOtherTypes(): array
    {
        return [
            ['day', 'day', [null, null]],
            ['day-1', 'day', ['-1', null]],
            ['day-1  sum', 'day', ['-1', 'sum']],
            ['day  sum', 'day', [null, 'sum']],
            ['week', 'week', [null, null]],
            ['week-1  sum', 'week', ['-1', 'sum']],
            ['month', 'month', [null, null]],
            ['month-1  sum', 'month', ['-1', 'sum']],
            ['comment', 'comment', [null]],
            ['comment-5', 'comment', ['-5']],
        ];
    }

    private static function executeParse(string $input): ParseResult
    {
        $parser = new Parser(PatternProvider::getPatterns());
        return $parser->parse($input);
    }

    private function assertParse(string $expectedCommand, array $expectedArgs, ParseResult $parseResult): void
    {
        self::assertSame($expectedCommand, $parseResult->getCommandName(), 'Expected command does not match');
        self::assertSame($expectedArgs, $parseResult->getArgs(), 'Expected command arguments does not match');
    }
}
