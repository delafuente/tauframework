<?php

session_start();
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
require_once( __ROOT__ . "/../../tau/inc/framework/TauResponse.php");

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
    "execute_sql",
    "filepath");
$replace_in_local = false;
$create_sql = false;
$execute_sql = false;
$full_filename = false;

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
            case "filepath": $full_filename = $input['content'];
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
        
        if(!isset($_SESSION['tokensFound'][$lang][$name])){
            $_SESSION['tokensFound'][$lang][$name]="";
        }
        
        //Check only new or changed content is updated
        if ($_SESSION['tokensFound'][$lang][$name] != $content) {
            if ($isNew) {
                $someInputIsNew = true;
                $createSQL .= "('$lang','$group','$name','" . addslashes($content) . "'),\n";
            } else {
                $updateSQL[] = str_replace($repUpdate, array(addslashes($content), $lang, $group, $name), $updateSqlTemplate);
            }
        }



        //file_put_contents("output.txt", "++ name:$name num:$num lang:$lang group:$group isNew:$isNew content:$content" . "\n", FILE_APPEND);
    }
}

$createSQL = $createSqlTemplate . trim($createSQL, ",\n") . ";\n";

//file_put_contents("output.txt", "Create SQL:\n" . $createSQL, FILE_APPEND);
//file_put_contents("output.txt", "Update SQL:\n" . implode("\n", $updateSQL), FILE_APPEND);

$operations = array();
$needToSave = ($execute_sql || $create_sql)?true:false;

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
} else {
    $operations['execute_sql'] = array("not_required");
}

if ($create_sql) {
    $operations['create_sql'] = array("success", $mainSQLArray);
} else {
    $operations['create_sql'] = array("not_required");
}

if($needToSave && defined('SAVE_GENERATED_SQL_FOLDER') && strlen(SAVE_GENERATED_SQL_FOLDER) > 3){
    
   
    $fileToWriteOn = SAVE_GENERATED_SQL_FOLDER . "/" . Tau::tau_get_group($full_filename) . "_" . time() . ".sql";
    
    $saveSQL = "-- TauFramework auto-generated sql to modify translations \n";
    $saveSQL .= "-- generated on " . date("Y-m-d H:m:s",time()) . " \n\n";
    
    foreach ($mainSQLArray as $sqlSentence){
        $saveSQL .= $sqlSentence . "\n";
    }
    if(!is_writable(SAVE_GENERATED_SQL_FOLDER)){
        @chmod(SAVE_GENERATED_SQL_FOLDER, 777);
    }
    if(file_exists(SAVE_GENERATED_SQL_FOLDER) && is_writable(SAVE_GENERATED_SQL_FOLDER)){
        file_put_contents($fileToWriteOn,$saveSQL);
    }
    
}

if ($replace_in_local) {

    if ($full_filename && file_exists($full_filename)) {

        //Find and replace all tokens in file template
        $file_contents = file_get_contents($full_filename);
        $originalSha1 = sha1($file_contents);

        //Found tokens
        preg_match_all("/\{\{[^\}]*\}\}/", $file_contents, $matches);
        $tokens = array();
        $tokensFound = array();
        foreach ($matches as $match) {

            foreach ($match as $coincidence) {
                $file_contents = str_replace($coincidence, "{{" . Tau::tau_tokenizer($full_filename, $coincidence) . "}}", $file_contents);
            }
        }

        $endSha1 = sha1($file_contents);

        if ($endSha1 != $originalSha1) {
            file_put_contents($full_filename, $file_contents);
            $operations['replace_in_local'] = array("success", $file_contents);
        } else {
            $operations['replace_in_local'] = array("success", "The file was not modified, because all language constants were already in tau format");
        }
    } else {
        $operations['replace_in_local'] = array("error", "Not filename received or file '$full_filename' does not exist");
    }
} else {
    $operations['replace_in_local'] = array("not_required");
}

echo json_encode($operations);
?>
