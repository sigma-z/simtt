<?php
declare(strict_types=1);

namespace Simtt\Command;

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
class PatternProvider
{

    public static function getPatterns(): array
    {
        $sp = '\s+';
        $timePattern = "({$sp}\d{1,2}:?\d{2})?";
        $taskPattern = "({$sp}.+)?";
        $minusNum = "(-\d+)?";
        return [
            'start' => '(start)' . $timePattern . $taskPattern,
            'stop' => '(stop)' . $timePattern . $taskPattern,
            'status' => '(status)',
            'tasks' => '(tasks)',
            'comment' => "(comment){$minusNum}",
            'log' => "(log)({$sp}\d+-\d+|{$sp}\d+|{$sp}all)?({$sp}asc|{$sp}desc)?",
            'day' => "(day){$minusNum}({$sp}sum)?",
            'week' => "(week){$minusNum}({$sp}sum)?",
            'month' => "(month){$minusNum}({$sp}sum)?",
            'exit' => '(exit|quit|q)',
            //'config' => '(config)',
        ];
    }
}
