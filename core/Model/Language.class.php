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
    public function getAvailableLanguages()
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
     * @return array - loaded language
     */
    public function getLanguageArray(string $language = 'de')
    {
        switch ($language) {
            case 'de':
                require_once(DOCUMENT_ROOT . '/data/lang/de.php');
                break;
            case 'en':
                require_once(DOCUMENT_ROOT . '/data/lang/en.php');
                break;
            default:
                require_once(DOCUMENT_ROOT . '/data/lang/de.php');
        }

        return $lang;
    }
}