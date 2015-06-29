<?php

namespace Prestatool\Services;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class PrestashopFinderService
{

    protected static $root_dir = '';

    private static $_instance;

    /**
     * get singleton instance
     *
     * @return mixed
     */
    public static function getInstance()
    {
        if( ! static::$_instance )
            static::$_instance = new static();

        return static::$_instance;
    }

    /**
     * Protected constructor to ensure proper calling through singleton accessor
     */
    protected function __construct() {

        // Search for root folder on construction so it is available for later calls to functions
        $this->getRootFolder();
    }

    /**
     * Map of folders which are expected to exist inside a prestashop installation
     * @var array
     */
    protected $folder_map = [
        'classes',
        'config',
        'controllers',
        'img',
        'mails',
        'modules',
        'override',
        'themes'
    ];

    /**
     * Attempt to find the base directory of the prestashop installation in the current path
     *
     * @throws FileNotFoundException
     */
    protected function getRootFolder()
    {
        $dirname = getcwd();

        while( ! $this->isPrestashopFolder($dirname) ) {
            // Check if we are at system root
            if( realpath('/') == $dirname ) {
                throw new FileNotFoundException('No prestashop installation found in the current path');
            }

            // Move up one level
            $dirname = dirname($dirname);
        }

        // Append directory separator
        $dirname = $dirname . DS;

        // Cache root directory for later use
        static::$root_dir = $dirname;
    }

    public function getSettingsFile()
    {
        $fs = new Filesystem();

        // Use cached path in which prestashop should exist
        $settings_path = static::$root_dir . 'config' . DS . 'settings.inc.php';

        // Check if configuration file exists
        if( ! $fs->exists($settings_path)) {
            throw new FileNotFoundException('settings.inc.php was not found.');
        }

        // Get contents of settings file
        $settings = file_get_contents($settings_path);

        return $settings;
    }

    /**
     * Check if a given directory contains the folders required for a prestashop installation
     *
     * @param $directory string
     * @return bool
     */
    protected function isPrestashopFolder($directory)
    {
        // Check for existence of folders in folder map
        foreach($this->folder_map as $folder) {

            $path = sprintf('%s%s%s', $directory, DIRECTORY_SEPARATOR, $folder);

            // One of the required folders is not found, so this can't be a prestashop folder
            if( ! is_dir($path) ) {
                return false;
            }
        }

        // return result
        return true;
    }
}