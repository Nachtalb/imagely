<?php

/**
 * @copyright   Nick Espig <nickespig.xyz>
 * @author      Nick Espig <info@nickespig.xyz>
 * @package     Imagely
 * @version     1.0
 * @subpackage  core
 */

function init()
{
    if (version_compare(PHP_VERSION, '7.0.3', '<')) {
        die('<span style="font-family: sans-serif"><span style="font-weight: bold;">Imagely &copy;</span> ' .
            'needs at leas <a target="_blank" href="https://secure.php.net/releases/#7.0.3">PHP 7.0.3</a>. ' .
            'You have PHP <a target="_blank" href="https://secure.php.net/releases/#' . PHP_VERSION . '">' . PHP_VERSION . '</a> installed.</span>');
    }

    require_once dirname(__DIR__) . '/config/configuration.php';
    require_once DOCUMENT_ROOT . '/lib/php/additionalPHPFunctions.php';

    require_once DOCUMENT_ROOT . '/core/Controller/Imagely.class.php';

    $controller = new Imagely();

    if (!isset($_SESSION['SESSION_VARS'])) $_SESSION['SESSION_VARS'] = [];

    $controller->getPage();
}
