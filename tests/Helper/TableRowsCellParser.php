<?php
declare(strict_types=1);

namespace Test\Helper;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class TableRowsCellParser
{

    public static function parse(string $content, bool $hasSumRow = false): array
    {
        $sumRow = $hasSumRow ? 2 : 0;
        $rows = explode("\n", $content);
        $rows = array_slice($rows, 3, count($rows) - 5 - $sumRow);    // header and closing line plus one for the offset
        return self::splitRowsIntoCells($rows);
    }

    public static function parseSumRow(string $content): array
    {
        $rows = explode("\n", $content);
        $rows = array_slice($rows, count($rows) - 3, 1);    // sum row
        return current(self::splitRowsIntoCells($rows));
    }

    private static function splitRowsIntoCells(array $rows): array
    {
        $rowsData = [];
        foreach ($rows as $row) {
            $rowsData[] = array_map('trim', explode('|', trim($row, '|')));
        }
        return $rowsData;
    }

}
