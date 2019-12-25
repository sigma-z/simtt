<?php
declare(strict_types=1);

/**
 * @author Steffen Zeidler <sigma_z@sigma-scripts.de>
 */
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/Helper/DIContainer.php';
require_once __DIR__ . '/Helper/LogEntryCreator.php';
require_once __DIR__ . '/Helper/VirtualFileSystem.php';

\Helper\DIContainer::$container = $containerBuilder;
