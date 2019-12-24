<?php
declare(strict_types=1);
/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 * @date   22.12.19
 */

namespace Test\Application\Command;

use Helper\DIContainer;
use PHPUnit\Framework\TestCase;
use Simtt\Infrastructure\Service\LogFile;
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
        $persister = new LogFile(new \DateTime(), VirtualFileSystem::LOG_DIR);
        $application = new Application('Simtt');
        $application->setAutoExit(false);
        /** @noinspection PhpParamsInspection */
        $application->add(DIContainer::$container->get('start.cmd'));
        $input = new StringInput('start 930');
        $application->run($input);

        $logEntry = LogEntryCreator::create('9:30');
        self::assertStringEqualsFile($persister->getFile(),  $logEntry . "\n");
    }
}
