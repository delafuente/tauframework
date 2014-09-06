<?php

/**
 * 
 * @abstract tau
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 05-jul-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

define('__ROOT__', str_replace("\\","/",dirname(dirname(__FILE__))) );

require_once( __ROOT__ . "/tau/inc/config.php");
require_once( __ROOT__ . "/tau/Tau.php" );
require_once( __ROOT__ . "/tau/inc/DataManager.php");

$tau = new Tau();

echo "<h3>" . Tau::getTauGreek() . " Framework working: </h3><br/>";

echo "<p>Current environment: " . $tau->getEnvironment() . "</p>";

$all_constants = get_defined_constants(true);
//print_r($all_constants);

$db1 = DataManager::getInstance();
echo "<p>db1 name: " . $db1->getDataBaseName() . "</p>";
echo "<code>db1: " . $db1->getVar("select text from breaking_news limit 1;") . "</code>";

$db2 = DataManager::getInstance("scheme");
echo "<p>db2 name: " . $db2->getDataBaseName() . "</p>";
echo "<code>db2: " . $db2->getVar("select twitter from customers limit 1;") . "</code>";

echo "<p>db1 name: " . $db1->getDataBaseName() . "</p>";

$db3 = DataManager::getInstance();
echo "<h4> After db3 = DataManager::getInstance()</h4>";

echo "<p>db3 name: " . $db3->getDataBaseName() . "</p>";
echo "<code>db3: " . $db3->getVar("select text from breaking_news limit 1;") . "</code>";

echo "<p>db1 name: " . $db1->getDataBaseName() . "</p>";
echo "<code>db1: " . $db1->getVar("select text from breaking_news limit 1;") . "</code>";


echo "<p>db2 name: " . $db2->getDataBaseName() . "</p>";
echo "<code>db2: " . $db2->getVar("select twitter from customers limit 1;") . "</code>";


$db1->close();
$db2->close();
$db3->close();

echo "<hr/>";
echo "<p>Powered by " . Tau::getTauFrameworkGreek() . ".</p>";
?>
