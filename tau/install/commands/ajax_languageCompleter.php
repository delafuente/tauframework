<?php

/**
 * 
 * @abstract tau
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 07-sep-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
define('__ROOT__', str_replace("\\", "/", dirname(dirname(__FILE__))));

require_once( __ROOT__ . "/../../tau/inc/config.php");
require_once( __ROOT__ . "/../../tau/inc/InputValidator.php");
require_once( __ROOT__ . "/../../tau/Tau.php" );
require_once( __ROOT__ . "/../../tau/inc/DataManager.php");

if (APPLICATION_ENVIRONMENT != 'local') {
    echo '<p>Cannot access this file</p>';
    die();
}

$post = $_POST;
$tau = Tau::getInstance();
$db = DataManager::getInstance();

$checks = array(
    "replace_in_local",
    "create_sql",
    "execute_sql");
$replace_in_local = false;
$create_sql = false;
$execute_sql = false;

$inputs = json_decode($_POST['frmData'], true);
$mainData = array();
$createSqlTemplate = "insert into tau_translations(lang,t_group,item,content) values \n";
$updateSqlTemplate = "update tau_translations set content='rep_content' where lang='rep_lang' and t_group='rep_group' and item='rep_item' limit 1;\n";
$createSQL = "";
$updateSQL = array();
$repUpdate = array('rep_content', 'rep_lang', 'rep_group', 'rep_item');
$someInputIsNew = false;

foreach ($inputs as $input) {
    //file_put_contents("output.txt", $input['name'] . "\n", FILE_APPEND);

    if (in_array($input['name'], $checks)) {
        switch ($input['name']) {
            case "replace_in_local": $replace_in_local = true;
                break;
            case "create_sql": $create_sql = true;
                break;
            case "execute_sql": $execute_sql = true;
                break;
        }
    } else {

        $spl = explode("-", $input['name']);
        $name = $spl[0];
        $num = $spl[1];
        $lang = $spl[2];
        $group = substr($name, 0, 11);
        $isNew = $input['isNew'];
        $content = $input['content'];
        if ($isNew) {
            $someInputIsNew = true;
            $createSQL .= "('$lang','$group','$name','" . addslashes($content) . "'),\n";
        } else {
            $updateSQL[] = str_replace($repUpdate, array(addslashes($content), $lang, $group, $name), $updateSqlTemplate);
        }

        //file_put_contents("output.txt", "++ name:$name num:$num lang:$lang group:$group isNew:$isNew content:$content" . "\n", FILE_APPEND);
    }
}

$createSQL = $createSqlTemplate . trim($createSQL, ",\n") . ";\n";

//file_put_contents("output.txt", "Create SQL:\n" . $createSQL, FILE_APPEND);
//file_put_contents("output.txt", "Update SQL:\n" . implode("\n", $updateSQL), FILE_APPEND);

$operations = array();

$mainSQLArray = $updateSQL;
if ($someInputIsNew) {
    $mainSQLArray[] = $createSQL;
}

if ($execute_sql) {
    
    $result = $db->makeTransaction($mainSQLArray);

    if ($result) {
        $operations['execute_sql'] = array("success", "SQL insertion OK");
    } else {
        $operations['execute_sql'] = array("error", $db->getLastErrorMessage());
    }
}else{
    $operations['execute_sql'] = array("not_required");
}

if ($create_sql) {
    $operations['create_sql'] = array("success", $mainSQLArray);
}else{
    $operations['create_sql'] = array("not_required");
}

echo json_encode($operations);
//echo $_POST['frmData'];
?>
