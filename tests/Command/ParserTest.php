<?php
declare(strict_types=1);

namespace Command;

use PHPUnit\Framework\TestCase;
use Simtt\Command\Parser;
use Simtt\Command\ParseResult;
use Simtt\Command\PatternProvider;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class ParserTest extends TestCase
{

    /**
     * @dataProvider provideParseStart
     * @param string $input
     * @param string $expectedCommand
     * @param array  $expectedArgs
     */
    public function testParseStart(string $input, string $expectedCommand, array $expectedArgs): void
    {
        $parseResult = self::executeParse($input);
        $this->assertParse($expectedCommand, $expectedArgs, $parseResult);
    }


    public function provideParseStart(): array
    {
        return [
            ['  start  ', 'start', []],
            ['start 921', 'start', ['921']],
            ['start 2221', 'start', ['2221']],
            ['start  2221  Task title sample', 'start', ['2221', 'Task title sample']],
            ['start Task title sample', 'start', ['Task title sample']],
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
            ['log', 'log', []],
            ['log 100', 'log', ['100']],
            ['log all', 'log', ['all']],
            ['log 100-100', 'log', ['100-100']],
            ['log 100-120', 'log', ['100-120']],
            ['log 100-120 desc', 'log', ['100-120', 'desc']],
            ['log 100-120 asc', 'log', ['100-120', 'asc']],
            ['log 100 asc', 'log', ['100', 'asc']],
        ];
    }

    /**
     * @dataProvider provideParseDateType
     * @param string $input
     * @param string $expectedCommand
     * @param array  $expectedArgs
     */
    public function testParseDateType(string $input, string $expectedCommand, array $expectedArgs): void
    {
        $parseResult = self::executeParse($input);
        $this->assertParse($expectedCommand, $expectedArgs, $parseResult);
    }

    public function provideParseDateType(): array
    {
        return [
            ['day', 'day', []],
            ['day-1', 'day', ['-1']],
            ['day-1  sum', 'day', ['-1', 'sum']],
            ['week', 'week', []],
            ['week-1  sum', 'week', ['-1', 'sum']],
            ['month', 'month', []],
            ['month-1  sum', 'month', ['-1', 'sum']],
        ];
    }


    ///**
    // * @dataProvider provideParseSimpleCommand
    // * @param string $input
    // * @param string $expectedCommand
    // */
    //public function testParseSimpleCommand(string $input, string $expectedCommand): void
    //{
    //    $parser = new Parser();
    //    $patterns = Simtt::getPatterns();
    //    foreach ($patterns as $pattern) {
    //        $parseResult = $parser->parse($pattern, $input);
    //        if ($parseResult) {
    //            self::assertSame($expectedCommand, $parseResult->getCommandName());
    //        }
    //    }
    //}

    private static function executeParse(string $input): ParseResult
    {
        $parser = new Parser(PatternProvider::getPatterns());
        return $parser->parse($input);
    }

    private function assertParse(string $expectedCommand, array $expectedArgs, ParseResult $parseResult): void
    {
        self::assertSame($expectedCommand, $parseResult->getCommandName());
        self::assertSame($expectedArgs, $parseResult->getArgs());
    }
}
