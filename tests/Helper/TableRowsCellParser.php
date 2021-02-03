<?php
declare(strict_types=1);

namespace Test\Helper;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class TableRowsCellParser
{

    public static function parse(string $content, int $onlyRowsWithNumberOfCells, bool $hasSumRow = false): array
    {
        $sumRow = $hasSumRow ? 2 : 0;
        $rows = explode(PHP_EOL, $content);
        $rows = array_slice($rows, 3, count($rows) - 5 - $sumRow);    // header and closing line plus one for the offset
        return self::splitRowsIntoCells($rows, $onlyRowsWithNumberOfCells);
    }

    public static function parseSumRow(string $content): array
    {
        $rows = explode(PHP_EOL, $content);
        $rows = array_slice($rows, count($rows) - 3, 1);    // sum row
        return current(self::splitRowsIntoCells($rows, 4));
    }

    private static function splitRowsIntoCells(array $rows, int $onlyRowsWithNumberOfCells): array
    {
        $rowsData = [];
        foreach ($rows as $row) {
            $cells = explode('|', trim($row, '|'));
            if (count($cells) === $onlyRowsWithNumberOfCells) {
                $rowsData[] = array_map('trim', $cells);
            }
        }
        return $rowsData;
    }

}
