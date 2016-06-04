<?php

/**
 * @copyright   Nick Espig <nickespig.xyz>
 * @author      Nick Espig <info@nickespig.xyz>
 * @package     imagely
 * @version     1.0
 * @subpackage  config
 */

/**
 * configuration.php:
 * set global constants, set global variables, set connection to database
 */

//Set global constants
define('PROTOCOL', 'http');
define('PATH', '');
define('PATH_OFFSET', '');
define('DOCUMENT_ROOT', dirname(__DIR__));

// Set default Language
$CONFIG['DEFAULT']['LANG'] = 'de';

//Set connection to database
$CONFIG['DB']['HOST']     = 'localhost';
$CONFIG['DB']['USER']     = 'imagely';
$CONFIG['DB']['PASSWORD'] = 'Hundescheisse123';
$CONFIG['DB']['NAME']     = 'imagely';

try {
    $GLOBALS['db'] = new PDO("mysql:host=" . $CONFIG['DB']['HOST'] . ";dbname=" . $CONFIG['DB']['NAME'], $CONFIG['DB']['USER'], $CONFIG['DB']['PASSWORD']);
    $GLOBALS['db']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $GLOBALS['db']->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    $GLOBALS['db']->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES UTF8');

} catch (PDOException $e) {
    throw new PDOException("Error  : " . $e->getMessage());
}

$GLOBALS['CONFIG'] = $CONFIG['DEFAULT'];