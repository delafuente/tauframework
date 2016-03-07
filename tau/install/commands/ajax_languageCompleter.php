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
$relativeFileName = false;

$inputs = json_decode($_POST['frmData'], true);
$mainData = array();
$createSqlTemplate = "insert ignore into tau_translations(lang,t_group,item,content) values \n";
$updateSqlTemplate = "update tau_translations set content='rep_content' where lang='rep_lang' and t_group='rep_group' and item='rep_item' limit 1;\n";
$rollbackSqlTemplate = "delete from tau_translations where lang='rep_lang' and t_group='rep_group' and item='rep_item' limit 1;\n";
$createSQL = "";
$updateSQL = array();
$repUpdate = array('rep_content', 'rep_lang', 'rep_group', 'rep_item');
$repRollback = array('rep_lang', 'rep_group', 'rep_item');
$someInputIsNew = false;
$rollbackSentences = array();

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
                $createSQL .= "('$lang','$group','$name','" . $db->escape($content) . "'),\n";
                $rollbackSentences[] = str_replace(
                                    $repRollback, 
                                    array($lang, $group, $name), 
                                    $rollbackSqlTemplate
                                    );
            } else {
                $updateSQL[] = str_replace($repUpdate, array($db->escape($content), $lang, $group, $name), $updateSqlTemplate);
                $currentContent = $db->getVar(
                        "select content from tau_translations".
                        " where lang='$lang' and t_group='$group' ".
                        "and item='$name' limit 1;"
                        );
                $rollbackSentences[] = str_replace($repUpdate, array($db->escape($currentContent), $lang, $group, $name), $updateSqlTemplate);
            }
        }



        //file_put_contents("output.txt", "++ name:$name num:$num lang:$lang group:$group isNew:$isNew content:$content" . "\n", FILE_APPEND);
    }
}

if($full_filename){
    $relativeFileName = str_replace(APPLICATION_PATH, "", $full_filename);
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

$user = strtolower(str_replace("\$","", getenv("username")));
$curTime = time();
if($user == ""){
    $user = get_current_user();
}
if($user == ""){
    $user = 'unknown';
}
$when = date("Y-m-d H:i:s", $curTime);
$dateFormatted = date("Ymd", $curTime);
$timeFormatted = date("His", $curTime);

$migrateName = $dateFormatted . "_" . 
$timeFormatted . "_translations_" . Tau::tau_get_group($relativeFileName); 

$fileToWriteOn = MIGRATES_FOLDER . "/" . $migrateName . ".sql"; 

if ($execute_sql) {
    
    $mainSQLArray['migrates'] = "insert into migrates (name,author,created,applied) values".
            "('$migrateName','$user','$when','$when');";
    
    $rollbackSentences['migrates'] = "delete from migrates where name='$migrateName' limit 1;";
    
    $result = $db->makeTransaction($mainSQLArray);

    unset( $mainSQLArray['migrates'] );
    
    if ($result) {
        $operations['execute_sql'] = array("success", "SQL insertion OK");
    } else {
        $operations['execute_sql'] = array("error", $db->getLastErrorMessage());
        $db->makeTransaction($rollbackSentences);
        unset( $rollbackSentences['migrates'] );
    }
} else {
    $operations['execute_sql'] = array("not_required");
}

if ($create_sql) {
    $operations['create_sql'] = array("success", $mainSQLArray);
} else {
    $operations['create_sql'] = array("not_required");
}

if($needToSave && defined('MIGRATES_FOLDER') && strlen(MIGRATES_FOLDER) > 3){
    
    $saveSQL = "-- $user@$when\n\n";
    $rollbackSQL = "-- rollback file from $user@$when\n\n";
    $totSentences = count($mainSQLArray);
    $counter = 0;
    foreach ($mainSQLArray as $sqlSentence){
        $counter++;
        $saveSQL .= $sqlSentence . "\n\n";
        if($counter != $totSentences){
            $saveSQL .= SQL_SPLIT . "\n\n";
        }
    }
    
    $totSentences = count($rollbackSentences);
    $counter = 0;
    foreach ($rollbackSentences as $sqlSentence){
        $counter++;
        $rollbackSQL .= $sqlSentence . "\n\n";
        if($counter != $totSentences){
            $rollbackSQL .= SQL_SPLIT . "\n\n";
        }
    }
    
    if(!is_writable(MIGRATES_FOLDER)){
        @chmod(MIGRATES_FOLDER, 777);
    }
    if(file_exists(MIGRATES_FOLDER) && is_writable(MIGRATES_FOLDER)){
        file_put_contents($fileToWriteOn,$saveSQL);
        file_put_contents(str_replace(".sql", "_rollback.sql", $fileToWriteOn), $rollbackSQL);
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
                
                $replaceWith = "{{" . Tau::tau_tokenizer($full_filename, $coincidence) . "}}";
                
                if(strpos($coincidence, '-') !== false 
                        || substr_count($replaceWith, 'tau_') > 1){
                    continue;
                }

                $file_contents = str_replace($coincidence, $replaceWith, $file_contents);
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