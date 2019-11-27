<?php
declare(strict_types=1);

namespace Command;

use PHPUnit\Framework\TestCase;
use Simtt\Command\Parser;
use Simtt\Command\Simtt;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class ParseTest extends TestCase
{

    /**
     * @dataProvider provideParse
     * @param string $pattern
     * @param string $input
     * @param string $expectedCommand
     * @param array  $expectedArgs
     */
    public function testParse(string $pattern, string $input, string $expectedCommand, array $expectedArgs): void
    {
        $parser = new Parser();
        $parseResult = $parser->parse($pattern, $input);
        self::assertSame($expectedCommand, $parseResult->getCommandName());
        self::assertSame($expectedArgs, $parseResult->getArgs());
    }

    /**
     * @return array
     */
    public function provideParse(): array
    {
        return [
            [Simtt::INTERACTIVE_COMMAND_PATTERN['start'], 'start', 'start', []],
            [Simtt::INTERACTIVE_COMMAND_PATTERN['start'], 'start 921', 'start', ['921']],
            [Simtt::INTERACTIVE_COMMAND_PATTERN['start'], 'start 2221', 'start', ['2221']],
            [Simtt::INTERACTIVE_COMMAND_PATTERN['start'], 'start 2221 Task title sample', 'start', ['2221', 'Task title sample']],
            [Simtt::INTERACTIVE_COMMAND_PATTERN['start'], 'start 921 Task title sample', 'start', ['921', 'Task title sample']],
        ];
    }

}
