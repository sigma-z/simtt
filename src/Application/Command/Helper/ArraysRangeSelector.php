<?php
declare(strict_types=1);

namespace Simtt\Application\Command\Helper;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class ArraysRangeSelector
{

    /** @var int */
    private $start;

    /** @var int */
    private $end;

    public function __construct(int $start, int $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @param LogFileEntriesFetcher $logEntriesFetcher
     * @return array
     */
    public function getElements(LogFileEntriesFetcher $logEntriesFetcher): array
    {
        $elements = [];
        $cursor = 1;
        while ($array = $logEntriesFetcher->fetch()) {
            $numberOfElements = count($array);
            if ($cursor + $numberOfElements < $this->start) {
                $cursor += $numberOfElements;
                continue;
            }
            $start = $this->start - $cursor >= 0 ? $this->start - $cursor : 0;
            $length = $this->end === 0 ? null : ($this->end - $this->start) + 1 - count($elements);
            $slicedElements = array_slice($array, $start, $length);
            $elements = array_merge($elements, $slicedElements);
            $cursor += $numberOfElements;
            if ($this->end !== 0 && $cursor > $this->end) {
                break;
            }
        }

        return $elements;
    }

}
