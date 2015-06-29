<?php

namespace Prestatool\Commands;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Prestatool\Services\PrestashopFinderService;
use Prestatool\Services\SettingsParserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DisplayConfigurationCommand extends Command
{

    /**
     * @var string Database table prefix used in queries
     */
    protected $db_prefix;

    /**
     * @var PrestashopFinderService
     */
    private $finderService;

    /**
     * @var SettingsParserService
     */
    private $parserService;

    public function __construct(PrestashopFinderService $finder_service, SettingsParserService $parser_service)
    {
        $this->finderService = $finder_service;
        $this->parserService = $parser_service;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('configuration:list')
            ->setDescription('Display items in the configuration table')
            ->addOption('id_shop', null, InputOption::VALUE_REQUIRED, 'Shop ID to fetch configuration items for. Defaults to all items', null)
            ->addArgument('search_prefix', InputArgument::OPTIONAL, 'Prefix to use when listing keys, only keys that start with this are displayed', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check if option id_shop is used
        $id_shop = false;
        if ( $input->hasOption('id_shop') ) {
            $id_shop = $input->getOption('id_shop');
        }

        // Check if argument search_prefix is used
        $search_prefix = false;
        if ( $input->hasArgument('search_prefix') ) {
            $search_prefix = $input->getArgument('search_prefix');
        }

        $connection = $this->getDBALConnection();

        $table_prefix = $this->db_prefix;
        $query = $this->getQuery($table_prefix, $id_shop, $search_prefix);

        $items = $connection->fetchAll($query);

        $items = $this->trimConfigurationItems($items);

        $this->renderTable($output, $items);
    }

    /**
     * Get connection through DBAL
     *
     * @return Connection
     * @throws DBALException
     */
    protected function getDBALConnection()
    {
        $settings = $this->finderService->getSettingsFile();
        $configuration = $this->parserService->parseSettings($settings);

        $conn = DriverManager::getConnection([
            'dbname'   => $configuration['_DB_NAME_'],
            'user'     => $configuration['_DB_USER_'],
            'password' => $configuration['_DB_PASSWD_'],
            'host'     => $configuration['_DB_SERVER_'],
            'driver'   => 'pdo_mysql'
        ]);

        $this->db_prefix = $configuration['_DB_PREFIX_'];

        return $conn;
    }

    /**
     * get Configuration table selection query
     *
     * @param $table_prefix
     * @param $id_shop
     * @param $search_prefix
     * @return string
     */
    protected function getQuery($table_prefix, $id_shop, $search_prefix)
    {
        $query = "SELECT `id_configuration`, `id_shop_group`, `id_shop`, `name`, `value` FROM `{$table_prefix}configuration` WHERE 1=1 ";

        if ( $id_shop ) {
            $query .= " AND (`id_shop` = {$id_shop} OR `id_shop` IS NULL) ";
        }

        if ( $search_prefix ) {
            $query .= " AND `name` LIKE '{$search_prefix}%'";
        }

        $query .= " ORDER BY `id_configuration`";

        return $query;
    }

    /**
     * Trim items longer as 32 characters
     *
     * @param $items
     * @return array
     */
    protected function trimConfigurationItems($items)
    {
        $items = array_map(function ($items)
        {
            $items = array_map(function ($item)
            {
                if ( strlen($item) >= 32 )
                {
                    $item = substr($item, 0, 29) . '...';
                }

                return $item;
            }, $items);

            return $items;
        }, $items);

        return $items;
    }

    /**
     * Render output table
     *
     * @param OutputInterface $output
     * @param $items
     */
    protected function renderTable(OutputInterface $output, $items)
    {
        $tableHelper = new Table($output);
        $tableHelper->setHeaders(['id_configuration', 'id_shop_group', 'id_shop', 'name', 'value'])
            ->setRows($items)
            ->render();
    }
}