<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   15.01.20
 */

namespace Test\Application\Command;


use Helper\DIContainer;
use Simtt\Infrastructure\Service\Clock\FixedClock;

class NowTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        DIContainer::$container->set('clock', new FixedClock(new \DateTime('2020-03-20 12:00:00')));
    }

    protected function getCommandShortName(): string
    {
        return 'now.cmd';
    }

    public function testNow(): void
    {
        $output = $this->runCommand('now');
        self::assertSame('NOW: 2020-03-20 12:00', rtrim($output->fetch()));
    }

}
