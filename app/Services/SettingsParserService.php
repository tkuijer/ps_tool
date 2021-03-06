<?php

namespace Prestatool\Services;

class SettingsParserService
{

    /**
     * Parse settings file contents by tokenizing and extracting the data we require
     *
     * @param $settings
     * @return array
     */
    public function parseSettings($settings)
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
                // Clean key
                $key = $this->cleanConfigurationValue($tokens[$k + 2][1]);

                $value = $this->cleanConfigurationValue($tokens[$k + 5][1]);

                // save token name and value
                $configuration[$key] = $value;
            }
        }

        return $configuration;
    }

    /**
     * Clean token, strip any surrounding whitespace or apostrophes
     *
     * @param $token
     * @return string
     */
    protected function cleanConfigurationValue($token)
    {
        // Trim whitespace
        $token = trim($token);

        // Remove unnecessary apostrophes
        $token = preg_replace('/\'/', '', $token);

        return $token;
    }
}