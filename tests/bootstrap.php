<?php
declare(strict_types=1);

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */

use Helper\DIContainer;
use Simtt\Infrastructure\Service\LogFile;
use Test\Helper\VirtualFileSystem;

require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/Helper/DIContainer.php';
require_once __DIR__ . '/Helper/LogEntryCreator.php';
require_once __DIR__ . '/Helper/TableRowsCellParser.php';
require_once __DIR__ . '/Helper/VirtualFileSystem.php';
require_once __DIR__ . '/Mock/SymfonyApplicationMock.php';
require_once __DIR__ . '/Application/Command/TestCase.php';

$containerBuilder->setParameter('config.logDir', VirtualFileSystem::LOG_DIR);
$containerBuilder->setParameter('currentLogFile', LogFile::createTodayLogFile(VirtualFileSystem::LOG_DIR));
DIContainer::$container = $containerBuilder;
