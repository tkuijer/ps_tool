<?php

namespace Prestatool\Commands;

use Prestatool\Services\PrestashopFinderService;
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

    public function __construct(PrestashopFinderService $finderService)
    {
        $this->finder_service = $finderService;

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
        $configuration = $this->parseSettings($settings);

        $this->renderTable($configuration);

    }

    /**
     * Parse settings file contents by tokenizing and extracting the data we require
     *
     * @param $settings
     * @return array
     */
    protected function parseSettings($settings)
    {
        // tokenize settings file contents
        $tokens = token_get_all($settings);
        $configuration = [];

        // walk through all tokens
        foreach ( $tokens as $k => $token )
        {
            if ( ! is_array($token) ) {
                continue;
            }

            // extract token type, and content
            list($id, $content) = $token;

            // Check to see if token is a define statement
            if ( $id == T_STRING && $content == 'define' ) {
                // save token name and value
                $configuration[$tokens[$k + 2][1]] = $tokens[$k + 5][1];
            }
        }

        return $configuration;
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