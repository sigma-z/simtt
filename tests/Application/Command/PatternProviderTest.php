<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   14.01.20
 */

namespace Application\Command;

use PHPUnit\Framework\TestCase;
use Simtt\Application\Command\PatternProvider;

class PatternProviderTest extends TestCase
{

    /**
     * @dataProvider provideIsTime
     * @param string $time
     * @param bool   $matches
     */
    public function testIsTime(string $time, bool $matches): void
    {
        self::assertSame($matches, PatternProvider::isTime($time));
    }

    public function provideIsTime(): array
    {
        return [
            ['', false],
            ['000', true],
            ['0:00', true],
            ['900', true],
            ['0900', true],
            ['2300', true],
            ['12:00', true],
            ['12|00', false],
            ['123|654', false],
        ];
    }

    /**
     * @dataProvider provideIsSelectionRange
     * @param string $selectionRange
     * @param bool   $matches
     */
    public function testIsSelectionRange(string $selectionRange, bool $matches): void
    {
        self::assertSame($matches, PatternProvider::isSelectionRangePattern($selectionRange));
    }

    public function provideIsSelectionRange(): array
    {
        return [
            ['', false],
            ['1', true],
            ['1111', true],
            ['11-11', true],
            ['1all', false],
            ['1-all', false],
            ['1-1-1', false],
            ['all', true],
            ['0-0', true],
        ];
    }

}
