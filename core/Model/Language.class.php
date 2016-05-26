<?php

/**
 * @copyright   Nick Espig <nicku8.com>
 * @author      Nick Espig <info@nicku8.com>
 * @package     imagely
 * @version     1.0
 * @subpackage  core
 */
class Language
{
    function getAvailableLanguages()
    {
        $availableLanguages = [
            'de',
            'en',
        ];

        return $availableLanguages;
    }

    function getLanguageArray($language)
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
        }

        return $lang;
    }
}