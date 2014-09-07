<?php

/**
 * 
 * @abstract tau
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 15-jul-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
define('__ROOT__', str_replace("\\","/",dirname(dirname(__FILE__))) );

require_once( __ROOT__ . "/../tau/inc/config.php");
require_once( __ROOT__ . "/../tau/Tau.php" );
require_once( __ROOT__ . "/../tau/inc/DataManager.php");
require_once( __ROOT__ . "/../tau/install/TauInstall.php");

$db = DataManager::getInstance();

if(APPLICATION_ENVIRONMENT != 'local'){
    echo '<p>Cannot access this file</p>';
    die();
}

echo "<h4>Deleting and recreating database test</h4>";

echo "<p>drop database result: " . $db->makeQuery("drop database test;") . "</p>";
echo "<p>create database result: " . $db->makeQuery("create database test;") . "</p>";


?>
