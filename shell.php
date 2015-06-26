<?php

use Symfony\Component\Console\Application;
use tkuijer\Commands\HelloWorldCommand;

require __DIR__ . '/vendor/autoload.php';

$app = new Application();
$app->addCommands([
    new HelloWorldCommand()
]);

$app->run();