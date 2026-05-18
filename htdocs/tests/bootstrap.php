<?php

declare(strict_types=1);
use Tester\Environment;

require __DIR__.'/../vendor/autoload.php';

Environment::setup();
Environment::setupFunctions();

register_shutdown_function(function () {
    Mockery::close();
});
