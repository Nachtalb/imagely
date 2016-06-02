<?php

/**
 * @copyright   Nick Espig <nickespig.xyz>
 * @author      Nick Espig <info@nickespig.xyz>
 * @package     imagely
 * @version     1.0
 * @subpackage  core
 */
class Language
{
    /**
     * Get all available languages as an array
     *
     * @return array - Array with all available language-shorthand
     */
    function getAvailableLanguages()
    {
        $availableLanguages = [
            'de',
            'en',
        ];

        return $availableLanguages;
    }

    /**
     * Loads the language file and gives back the loaded language-shorthand
     *
     * @param string [$language] - language-shorthand, default is de
     *
     * @return string - loaded language 
     */
    function getLanguageArray($language = 'de')
    {
        switch ($language) {
            case 'de':
                require_once(DOCUMENT_ROOT . '/data/lang/german.php');
                break;
            case 'en':
                require_once(DOCUMENT_ROOT . '/data/lang/english.php');
                break;
            default:
                require_once(DOCUMENT_ROOT . '/data/lang/german.php');
                $language = 'de';
        }

        return $language;
    }
}