<?php
declare(strict_types=1);

use Simtt\Application\Command\PatternProvider;
use Simtt\Infrastructure\Prompter\Prompter;
use Simtt\Infrastructure\Service\ConfigLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

define('APP_ROOT', __DIR__ . '/..');

$config = ConfigLoader::load(APP_ROOT . '/config.json', APP_ROOT);
$containerBuilder = new ContainerBuilder();
$containerBuilder->setParameter('config.precision', $config->getPrecision());
$containerBuilder->setParameter('config.logDir', $config->getLogDir());
$containerBuilder->setParameter('config.showLogItems', $config->getShowLogItems());
$containerBuilder->setParameter('config.showTaskItems', $config->getShowTaskItems());
$containerBuilder->setParameter('config.promptComment', $config->getPromptComment());
$containerBuilder->setParameter('parserPattern', PatternProvider::getPatterns());
$containerBuilder->set('prompter', Prompter::create());
$loader = new YamlFileLoader($containerBuilder, new FileLocator(APP_ROOT . '/src/Infrastructure'));
$loader->load('services.yaml');

unset($config, $loader);

return $containerBuilder;
