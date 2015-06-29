<?php

namespace Prestatool\Commands;

use Prestatool\Services\PrestashopFinderService;
use Prestatool\Services\SettingsParserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class DisplaySettingsCommand extends Command
{

    /** @var PrestashopFinderService $finder_service */
    protected $finder_service;

    /** @var string $basedir Directory in which the prestashop installation can be found */
    protected $basedir;

    /** @var OutputInterface $output */
    protected $output;

    /** @var InputInterface $input */
    protected $input;
    /**
     * @var SettingsParserService
     */
    private $parserService;

    public function __construct(PrestashopFinderService $finderService, SettingsParserService $parserService)
    {
        $this->finder_service = $finderService;
        $this->parserService = $parserService;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('settings:list')
            ->setDescription('Display an overview of configuration items which are set in the settings.inc.php file.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output  = $output;
        $this->input   = $input;

        $this->basedir = $this->finder_service->getRootFolder();
        $this->displaySettingsFile();
    }

    /**
     * Read settings.inc.php file contents, parse them and display them in tabular format
     */
    protected function displaySettingsFile()
    {
        $fs = new Filesystem();
        $settings_path = $this->basedir . 'config' . DS . 'settings.inc.php';

        // Check if configuration file exists
        if( ! $fs->exists($settings_path)) {
            throw new FileNotFoundException('settings.inc.php was not found.');
        }

        // Get contents of settings file
        $settings = file_get_contents($settings_path);
        $configuration = $this->parserService->parseSettings($settings);

        $this->renderTable($configuration);

    }

    /**
     * Render output table
     *
     * @param array $configuration
     */
    protected function renderTable($configuration)
    {
        // Transform array to something the table formatter can use
        $rows = [];
        foreach($configuration as $key => $item) {
            $rows[] = [$key, $item];
        }

        // Output table
        $table = new Table($this->output);
        $table
            ->setHeaders(['Key', 'Value'])
            ->setRows(
                $rows
            );

        $table->render();
    }
}