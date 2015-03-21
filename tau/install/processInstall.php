<?php

/**
 * 
 * @abstract tau
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 14-jul-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
define('__ROOT__', str_replace("\\", "/", dirname(dirname(__FILE__))));

require_once( __ROOT__ . "/../tau/inc/config.php");
require_once( __ROOT__ . "/../tau/Tau.php" );
require_once( __ROOT__ . "/../tau/inc/DataManager.php");

require_once( __ROOT__ . "/../tau/install/TauInstall.php");
require_once( __ROOT__ . "/../tau/inc/InputValidator.php");
require_once( __ROOT__ . "/../tau/inc/LogFile.php");
require_once( __ROOT__ . "/../tau/inc/framework/TauResponse.php");
require_once( __ROOT__ . "/../tau/inc/framework/TauRequest.php");
require_once( __ROOT__ . "/../tau/inc/framework/TauSession.php");

$tau = Tau::getInstance();
$tauInstall = new TauInstall();
$modules = array(); //array with selected modules

?>
<link rel="stylesheet" type="text/css" href="install.css">
<link rel="icon" type="image/png" href="/tau32g.png" sizes="32x32">
<link rel="icon" type="image/png" href="/tau64g.png" sizes="64x64">
<div class="main">
<?php


echo "<h3>" . Tau::getTauGreek() . " Framework installation: </h3><br/>";

echo "<p>Current environment: " . $tau->getEnvironment() . "</p>";
//echo error_level_tostring(error_reporting(), ',') . "<br/><br/>";
//Control if application is yet installed
if (file_exists(APPLICATION_PATH . "/tau/install/app_installed")) {
    echo "<p class='red'>Application is yet installed. Please remove install/app_installed file in order to reinstall the application.</p>";
    $tauInstall->endPage();
}

//Control if db access is working

$db = DataManager::getInstance();

$inputValidator = new InputValidator($_POST, true, $db);
$cleanInput = $inputValidator->getCleanArray();

if (!is_array($cleanInput)) {
    $cleanInput = array();
}

foreach ($cleanInput as $selKey => $selectedOptions) {

    if (strpos($selKey, "module_") !== false) {
        $newModule = substr($selKey, strlen("module_"));
        $modules[$newModule] = $newModule;
        echo "<p>Selected module for install <span class='success'>$newModule</span></p>";
    }
}
//Control if database tables are yet created, and create them otherwise
$createTables = $tauInstall->getAllTauTables();

$existingTables = $tauInstall->getExistingTables();

if (count($existingTables) > 0) {
    echo "<p>Existing tables: " . count($existingTables) . "</p>";
    foreach ($existingTables as $table) {
        echo "<p class='red'>table `$table` is yet created in db " . $db->getDataBaseName() . ", and needs to be removed before installation </p>";
    }
    $tauInstall->endPage();
} else {
    echo "<p>Creating tables...</p>";

    foreach ($createTables as $tableName => $newTable) {
        if ($tableName != "") {
            if (!controlModule($tableName, $modules)) {
                continue;
            }
            if ($db->makeQuery($newTable) !== false) {
                if (strpos($tableName, "ALTER ") !== false) {
                    $modifiedTable = substr($tableName, strlen("ALTER "));
                    echo "<p class='success'>Table $modifiedTable altered in database</p>";
                } else {
                    echo "<p class='success'>Table $tableName created in database</p>";
                }
            } else {
                echo "<p class='red'>Error inserting table $tableName : </p>";
                echo $db->getLastErrorMessage();
                $tauInstall->endPage();
            }
        }
    }

    echo "<p>Inserting data...</p>";
    $dataQuery = file_get_contents(APPLICATION_PATH . "/tau/install/data.sql");
    
    if ($db->makeQuery($dataQuery) !== false) {
        echo "<p class='success'>Data insertion OK !</p>";
        file_put_contents(__ROOT__ . "/../tau/install/app_installed", 
                date("Y-m-d H:i:s", time()) .
                "\n\nDelete this file if you want to run the installation again.");
    } else {
        echo "<p class='red'>Error inserting data : </p>";
        echo $db->getLastErrorMessage();
        $tauInstall->endPage();
    }
    
    if(controlModule("tau_uploads", $modules)){
        echo "<p>Creating uploads folder structure...</p>";
        include(APPLICATION_PATH . "/tau/install/build_uploads_folder_structure.php");
    }
    
    
}

echo "<p>Installation process finished</p>";

//Install modules

$tauInstall->endPage();

function controlModule($tableName, array &$moduleList) {

    if (strpos($tableName, "ALTER ") !== false) {
        $tableName = substr($tableName, strlen("ALTER "));
    }

    switch ($tableName) {
        case 'tau_friendship': return in_array('tau_friendship', $moduleList); break;
        case 'tau_gallery':
        case 'tau_photos': return in_array('tau_gallery', $moduleList); break;
        case 'tau_uploads': return in_array('tau_uploads', $moduleList); break;
        default:
            return true;
    }
    return true;
}
?>
