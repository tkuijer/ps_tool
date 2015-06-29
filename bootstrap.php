<?php

if( ! defined('DS') )
    define('DS', DIRECTORY_SEPARATOR);

use Prestatool\Services\PrestashopFinderService;
use Symfony\Component\Console\Application;
use Prestatool\Commands\DisplaySettingsCommand;

require __DIR__ . '/vendor/autoload.php';

$finder_service = new PrestashopFinderService();

$app = new Application();
$app->addCommands([
    new DisplaySettingsCommand($finder_service)
]);

return $app;
