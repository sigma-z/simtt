<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   22.12.19
 */

namespace Test\Application\Command;

use PHPUnit\Framework\TestCase;
use Simtt\Application\Command\Start;
use Simtt\Domain\TimeTracker;
use Simtt\Infrastructure\Service\LogHandler;
use Simtt\Infrastructure\Service\LogPersister;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Test\Helper\LogEntryCreator;
use Test\Helper\VirtualFileSystem;

class StartTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        VirtualFileSystem::setUpFileSystem();
    }

    protected function tearDown(): void
    {
        VirtualFileSystem::tearDownFileSystem();
        parent::tearDown();
    }

    public function testStart(): void
    {
        $persister = new LogPersister(VirtualFileSystem::LOG_DIR);
        $application = new Application('Simtt');
        $application->setAutoExit(false);
        $application->add(new Start($persister, new TimeTracker(new LogHandler())));
        $input = new StringInput('start 930');
        $application->run($input);

        $logEntry = LogEntryCreator::create('9:30');
        self::assertStringEqualsFile($persister->getFile(),  $logEntry . "\n");
    }
}
