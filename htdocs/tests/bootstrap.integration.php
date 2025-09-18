<?php declare(strict_types = 1);

namespace Tests;

use App\Bootstrap;
use App\Services\AppConfiguration;
use Tester\Environment;

require __DIR__ . '/../vendor/autoload.php';

Environment::setup();
Environment::setupFunctions();

register_shutdown_function(function () {
    \Mockery::close();
});


$configurator = Bootstrap::boot();
$container = $configurator->createContainer();

$GLOBALS['container'] = $container;
$testBucket =  'herbarium-test';
$GLOBALS['testBucket'] = $testBucket;

$appConfiguration = $container->getByType(AppConfiguration::class);
if ($appConfiguration->getPlatform() !== 'development') {
    die('do not run elsewhere!');
}





