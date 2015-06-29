<?php

namespace Prestatool\Services;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class PrestashopFinderService
{

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
     * @return string
     */
    public function getRootFolder()
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

        return $dirname . DIRECTORY_SEPARATOR;
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
            if( ! is_dir($path) ) {
                return false;
            }
        }

        // return result
        return true;
    }
}