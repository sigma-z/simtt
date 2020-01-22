<?php
declare(strict_types=1);

namespace Simtt\Application\Command;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class PatternProvider
{

    public static function getTimePattern(): string
    {
        return '\d{1,2}:?\d{2}';
    }

    public static function isTime(string $string): bool
    {
        $timePattern = self::getTimePattern();
        return preg_match("/^{$timePattern}$/", trim($string)) !== 0;
    }

    public static function getSelectionRangePattern(): string
    {
        return "\d+-\d+|\d+|all";
    }

    public static function isSelectionRangePattern(string $string): bool
    {
        $pattern = self::getSelectionRangePattern();
        return preg_match("/^({$pattern})$/", trim($string)) !== 0;
    }

    public static function getPatterns(): array
    {
        $sp = '\s+';
        $timePattern = self::getTimePattern();
        $timePattern = "({$sp}{$timePattern})?";
        $taskPattern = "({$sp}.+)?";
        $minusNum = "-?(\d+)?";
        return [
            'start' => '(start\*?)' . $timePattern . $taskPattern,
            'stop' => '(stop\*?)' . $timePattern . $taskPattern,
            'status' => '(status)',
            'tasks' => '(tasks)',
            'task' => "(task){$minusNum}",
            'comment' => "(comment){$minusNum}",
            'log' => "(log)({$sp}\d+-\d+|{$sp}\d+|{$sp}all)?({$sp}asc|{$sp}desc)?",
            'day' => "(day){$minusNum}({$sp}sum)?",
            'yesterday' => "(yesterday)({$sp}sum)?",
            'week' => "(week){$minusNum}({$sp}sum)?",
            'month' => "(month){$minusNum}({$sp}sum)?",
            'exit' => '(exit|quit|q)',
            'now' => '(now)',
            //'config' => '(config)',
        ];
    }
}
