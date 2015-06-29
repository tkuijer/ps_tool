<?php

if( ! defined('DS') )
    define('DS', DIRECTORY_SEPARATOR);

use Prestatool\Commands\DisplayConfigurationCommand;
use Prestatool\Services\PrestashopFinderService;
use Prestatool\Services\SettingsParserService;
use Symfony\Component\Console\Application;
use Prestatool\Commands\DisplaySettingsCommand;

require __DIR__ . '/vendor/autoload.php';

$parser_service = new SettingsParserService();

$app = new Application();
$app->addCommands([
    new DisplaySettingsCommand(PrestashopFinderService::getInstance(), $parser_service),
    new DisplayConfigurationCommand(PrestashopFinderService::getInstance(), $parser_service)
]);

return $app;
