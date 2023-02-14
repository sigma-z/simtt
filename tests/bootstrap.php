<?php
declare(strict_types=1);

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */

use Helper\DIContainer;
use Simtt\Infrastructure\Service\LogFile;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/** @var ContainerBuilder $containerBuilder */
$containerBuilder = require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/Helper/DIContainer.php';
require_once __DIR__ . '/Helper/LogEntryCreator.php';
require_once __DIR__ . '/Helper/TableRowsCellParser.php';
require_once __DIR__ . '/Helper/VirtualFileSystemTrait.php';
require_once __DIR__ . '/Mock/SymfonyApplicationMock.php';
require_once __DIR__ . '/Application/Command/TestCase.php';

const LOG_DIR = 'vfs://root' . DIRECTORY_SEPARATOR . 'logs';

$containerBuilder->setParameter('config.logDir', LOG_DIR);
$containerBuilder->setParameter('config.promptComment', true);
$containerBuilder->set('currentLogFile', LogFile::createTodayLogFile(LOG_DIR));
DIContainer::$container = $containerBuilder;
