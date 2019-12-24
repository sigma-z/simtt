<?php
declare(strict_types=1);

use Simtt\Infrastructure\Service\ConfigLoader;
use Simtt\Application\Command\PatternProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

define('APP_ROOT', __DIR__ . '/..');

$config = ConfigLoader::load(APP_ROOT . '/config.json');
$containerBuilder = new ContainerBuilder();
$containerBuilder->setParameter('today', new \DateTime());
$containerBuilder->setParameter('logDir', $config->getLogDir());
$containerBuilder->setParameter('parserPattern', PatternProvider::getPatterns());
$containerBuilder->setParameter('prompter', \Simtt\Infrastructure\Prompter\Prompter::create());
$loader = new YamlFileLoader($containerBuilder, new FileLocator(APP_ROOT . '/src/Infrastructure'));
$loader->load('services.yaml');

unset($config, $loader);
