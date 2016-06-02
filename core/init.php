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
    //Check php version (5.5.0 or newer is required)
    $php = phpversion();
    if (version_compare($php, '5.5.0') < 0) {
        die('imagely ben&ouml;tigt mindestens PHP in der Version 5.5.0.<br />Auf Ihrem System l&auml;uft PHP ' . $php);
    }

    //Include config
    require_once dirname(__DIR__) . '/config/configuration.php';

    //Include controller
    require_once DOCUMENT_ROOT . '/core/Controller/Imagely.class.php';


    $controller = new Imagely();

    if (!isset($_SESSION['SESSION_VARS'])) $_SESSION['SESSION_VARS'] = [];

    $controller->getPage();
}
