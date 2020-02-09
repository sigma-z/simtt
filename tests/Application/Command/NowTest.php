<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   15.01.20
 */

namespace Test\Application\Command;


class NowTest extends TestCase
{

    protected function getCommandShortName(): string
    {
        return 'now.cmd';
    }

    public function testNow(): void
    {
        $output = $this->runCommand('now');
        self::assertRegExp('/^NOW: \d{4}-\d{2}-\d{2} \d{2}:\d{2}\n$/', $output->fetch());
    }

}
