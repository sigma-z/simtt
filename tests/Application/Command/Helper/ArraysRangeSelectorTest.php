<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   01.02.20
 */

namespace Test\Application\Command\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Simtt\Application\Command\Helper\ArraysRangeSelector;
use Simtt\Application\Command\Helper\LogFileEntriesFetcher;
use Simtt\Domain\Model\LogFileInterface;

class ArraysRangeSelectorTest extends TestCase
{

    #[DataProvider('provideGetElements')]
    public function testGetElements(int $start, int $end, array $expectedElements): void
    {
        $arrays = [
            new LogFileEntriesFetcherMock(['4.1']),
            new LogFileEntriesFetcherMock(['3.1', '3.2', '3.3']),
            new LogFileEntriesFetcherMock(['2.1', '2.2']),
            new LogFileEntriesFetcherMock(['1.1', '1.2', '1.3', '1.4']),
        ];
        $logEntriesFetcher = new LogFileEntriesFetcher($arrays);
        $rangeSelector = new ArraysRangeSelector($start, $end);
        $elements = $rangeSelector->getElements($logEntriesFetcher);
        self::assertSame($expectedElements, $elements);
    }

    public static function provideGetElements(): array
    {
        return [
            [1, 15, ['1.1', '1.2', '1.3', '1.4', '2.1', '2.2', '3.1', '3.2', '3.3', '4.1']],
            [1, 0, ['1.1', '1.2', '1.3', '1.4', '2.1', '2.2', '3.1', '3.2', '3.3', '4.1']],
            [1, 1, ['1.1']],
            [1, 4, ['1.1', '1.2', '1.3', '1.4']],
            [1, 5, ['1.1', '1.2', '1.3', '1.4', '2.1']],
            [2, 5, ['1.2', '1.3', '1.4', '2.1']],
            [4, 5, ['1.4', '2.1']],
            [7, 10, ['3.1', '3.2', '3.3', '4.1']],
            [8, 10, ['3.2', '3.3', '4.1']],
        ];
    }

}

class LogFileEntriesFetcherMock implements LogFileInterface
{

    /** @var array */
    private $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function getEntries(): array
    {
        return $this->array;
    }
}
