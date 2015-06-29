<?php

namespace Prestatool\Commands;

use Prestatool\Services\PrestashopFinderService;
use Prestatool\Services\SettingsParserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisplaySettingsCommand extends Command
{

    /** @var PrestashopFinderService $finder_service */
    protected $finder_service;

    /** @var OutputInterface $output */
    protected $output;

    /** @var InputInterface $input */
    protected $input;
    /**
     * @var SettingsParserService
     */
    private $parser_service;

    public function __construct(PrestashopFinderService $finder_service, SettingsParserService $parser_service)
    {
        $this->finder_service = $finder_service;
        $this->parser_service = $parser_service;

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

        $this->displaySettingsFile();
    }

    /**
     * Read settings.inc.php file contents, parse them and display them in tabular format
     */
    protected function displaySettingsFile()
    {
        $settings      = $this->finder_service->getSettingsFile();
        $configuration = $this->parser_service->parseSettings($settings);

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