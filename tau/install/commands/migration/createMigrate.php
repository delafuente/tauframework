<?php
session_start();
/**
 * 
 * @abstract Create migrate files
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 30-ago-2015
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

define('__ROOT__', str_replace("\\","/",dirname(dirname(__FILE__))) );

require_once( __ROOT__ . "/../../../tau/inc/config.php");
require_once( __ROOT__ . "/../../../tau/inc/InputValidator.php");
require_once( __ROOT__ . "/../../../tau/Tau.php" );
require_once( __ROOT__ . "/../../../tau/inc/DataManager.php");
require_once( __ROOT__ . "/../../../tau/inc/framework/TauResponse.php");
require_once( __ROOT__ . "/../../../tau/inc/framework/TauSession.php");


$tau = Tau::getInstance();
$db = DataManager::getInstance();
$template = file_get_contents( APPLICATION_PATH ."/tau/install/commands/migration/assets/createMigrate.html");
$oInputValidator = new InputValidator($_GET);
$filteredGet = $oInputValidator->getCleanArray();

$userName = $filteredGet['userName'];

$migrateName = preg_replace("/[^\da-zA-Z0-9]+/i","_", $filteredGet['migrateName']);

$migDate = date("Y-m-d H:i:s");
$preName = date("Ymd_His");

$finalMigrateName = $preName.'_'.$migrateName.'.sql';
$finalRollbackName = $preName.'_'.$migrateName.'_rollback.sql';

$resultText = "<p>Migration create results:</p>";
$basePath = WEB_PATH . '/migrates/';

if(file_put_contents($basePath.$finalMigrateName, "-- $userName@$migDate \n\n" )){
    $resultText .= "<p class='success'>Migrate $finalMigrateName created with success</p>";
}else{
    $resultText .= "<p class='red'>Migrate $finalMigrateName cannot be created</p>";
}

if(file_put_contents($basePath.$finalRollbackName, "-- $userName@$migDate \n\n" )){
    $resultText .= "<p class='success'>Migrate $finalRollbackName created with success</p>";
}else{
    $resultText .= "<p class='red'>Migrate $finalRollbackName cannot be created</p>";
}

$resultText .= "<p>Process finished</p>";

$template = file_get_contents( APPLICATION_PATH ."/tau/install/commands/migration/assets/createMigrate.html");

$template = str_replace("{{replace_migrate_name}}", $finalMigrateName, $template);
$template = str_replace("{{replace_migrate_creation}}", $resultText, $template);

$template = str_replace("{{replace_footer}}", "Powered by " . Tau::getTauFrameworkGreek(), $template);

echo $template;