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

    public static function getPatterns(): array
    {
        $sp = '\s+';
        $timePattern = self::getTimePattern();
        $timePattern = "({$sp}{$timePattern})?";
        $stringPattern = "({$sp}.+)?";
        $minusNum = "-?(\d+)?";
        return [
            'start' => '(start\*?)' . $timePattern . $stringPattern,
            'stop' => '(stop\*?)' . $timePattern . $stringPattern,
            'status' => '(status)',
            'tasks' => "(tasks)({$sp}\d+)?",
            'task' => "(task){$minusNum}{$stringPattern}",
            'comment' => "(comment){$minusNum}{$stringPattern}",
            'log' => "(log)({$sp}\d+-\d+|{$sp}\d+|{$sp}all)?",
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
