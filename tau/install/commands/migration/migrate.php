<?php
session_start();
/**
 * 
 * @abstract Executes migrates in folder that are not in db
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 08-mar-2015
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
$template = file_get_contents( APPLICATION_PATH ."/tau/install/commands/migration/assets/migration.html");

if( !file_exists( MIGRATES_FOLDER ) ){
    $s = mkdir( MIGRATES_FOLDER, 0644 );
    $result = "<p class='red'>Error creating folder ".MIGRATES_FOLDER."</p>";
    if($s){
        $result = "<p class='success'>Created folder ".MIGRATES_FOLDER." with mode 0644</p>";
    }
    $out = "<p class='red'>Folder '" . MIGRATES_FOLDER . "' does not exist.</p>";
    $out .= $result;
    $template = str_replace("{{replace_folder_creation}}", $out, $template);
    
}else{
    $template = str_replace("{{replace_folder_creation}}", "", $template);
}

//Get pending migrates
$currentMigrates = DataManager::getInstance()->getResults("select * from migrates");

$listOfMigrates = array();
$listOfMigrates = scandir( MIGRATES_FOLDER );

$migratesToExecute = array();
$textMigratesToExecute = "";

foreach($listOfMigrates as $migrateCandidate){
    if($migrateCandidate == "." 
            || $migrateCandidate == ".." 
            || is_dir( $migrateCandidate ) 
            || strpos($migrateCandidate, "_rollback") !== false
            || !$currentMigrates){
        continue;
    }
    $found = false; 
    foreach($currentMigrates as $curMigrate){
        if($migrateCandidate == ($curMigrate['name'] . ".sql")){
            $found = true;
        }
    }
    if(!$found){
        $migratesToExecute[] = $migrateCandidate;
    }
}


if(empty($migratesToExecute)){
    $textMigratesToExecute .= "<p class='success'>No migrates pending</p>";
}else{
    foreach($migratesToExecute as $mig){
        $textMigratesToExecute .= "<p class='success'>$mig</p>";
    }
}
$template = str_replace("{{replace_pending_migrates}}", $textMigratesToExecute, $template);

$sqlErrors = "";
$culprit = "";
$culpritIns = "";
$initTime = microtime(true);
$migrationMessages = "";
$filesToRollback = array();
$rollbackMessages = "";
$migrationSuccess = 1;

if(isset($_GET['execute']) && $_GET['execute'] == 'yes' 
        && !empty($migratesToExecute) && $_GET['onlyonce'] == $_SESSION['onlyOnce']){
        
    $_SESSION['onlyOnce'] = "";
    
    foreach($migratesToExecute as $migrateFileName){

        $migrateContent = file_get_contents(MIGRATES_FOLDER . "/" . $migrateFileName);
        $filesToRollback[] = str_replace(".sql", "_rollback.sql", MIGRATES_FOLDER . "/" . $migrateFileName);
        $migrateContentSentences = explode(SQL_SPLIT, $migrateContent);

        foreach($migrateContentSentences as $migrateSentence){

            $migrateSentence = removeMysqlComments($migrateSentence);
            $migrateSentence = trim($migrateSentence);

            if($migrationSuccess){

                $migrationSuccess = DataManager::getInstance()->makeQuery($migrateSentence);

                if(!$migrationSuccess){
                    $res = DataManager::getInstance()->getLastErrorMessage();
                    if($res == 'NO ERROR'){
                        $migrationSuccess = true;
                    }else{
                        $culpritIns = $migrateSentence;
                    }
                }
            }
        }

        if (!$migrationSuccess) {
            $sqlErrors .= DataManager::getInstance()->getLastErrorMessage() . "<br/>";
            $culprit = $migrateFileName;
            break;
        }

        $line = fgets(fopen(MIGRATES_FOLDER . "/" . $migrateFileName, 'r'));
        $line = str_replace("-- ", "", $line);
        $lineParts = explode("@", $line);
        $userName = trim($lineParts[0]);
        $datetime = trim($lineParts[1]);
        if(empty($userName)){ $userName = "unknown"; }
        if(empty($datetime)){ $datetime = "0"; }
        $migrateName = str_replace(".sql", "", $migrateFileName);
        $currentDateTime = date("Y-m-d H:i:s", time());
        $insertMigrate = "insert ignore into migrates (name,author,created,applied) values".
            "('$migrateName','$userName','$datetime','$currentDateTime');";

        $migrationSuccess = DataManager::getInstance()->makeQuery($insertMigrate);

        if(!$migrationSuccess){
            $sqlErrors .= DataManager::getInstance()->getLastErrorMessage() . "<br/>";
            $culpritIns = $insertMigrate;
            break; 
        }

    }

    

    if(!$migrationSuccess){
        
        foreach($filesToRollback as $rollbackFile){
            
            if(file_exists($rollbackFile)){
                
                $rollbackContent = file_get_contents($rollbackFile);
                $rollbackContentSentences = explode(SQL_SPLIT, $rollbackContent);
                
                foreach($rollbackContentSentences as $rollbackSentence){
                    
                    $rollbackSentence = removeMysqlComments($rollbackSentence);
                    $rollbackSentence = trim($rollbackSentence);
                    $rollbackResult = DataManager::getInstance()->makeQuery($rollbackSentence);
                    
                    if(!$rollbackResult){
                        
                        $res = DataManager::getInstance()->getLastErrorMessage();
                        
                        if($res == 'NO ERROR'){
                            $migrationSuccess = true;
                        }else{
                            $rollbackMessages .= "<p class='red'>Error rolling back file ".
                            str_replace(MIGRATES_FOLDER ."/","", $rollbackFile) . "</p>";
                            $rollbackMessages .= "<p class='red'>Query: $rollbackSentence</p>";
                            $rollbackMessages .= "<p class='red'>Error reported: " . 
                            DataManager::getInstance()->getLastErrorMessage() . "</p>";
                        }
                        
                        
                    }else{
                        
                        $rollbackMessages .= "<p class='success'>Executed rollback sentence:</p>";
                        $rollbackMessages .= "<p class='constant'>$rollbackSentence</p>";
                    }
                }
                
            }else{
                $rollbackMessages  .= "<p class='red'>Tried to rollback: $rollbackFile but it doesn't exist</p>";
            }
        }
    }
    
    $elapsedTime = time() - $initTime;
    

    if(!$migrationSuccess){
        $migrationMessages .= "<p class='red'>Some errors happened: </p>";
        $migrationMessages .= "<p>$sqlErrors</p>";
        if($culprit != ""){
            $migrationMessages .= "<p>Error happened while processing file $culprit</p>";
        }
        if($culpritIns != ""){
            $migrationMessages .= "<p>Error happened while executing the following sql:</p>";
            $migrationMessages .= "<p class='constant'>$culpritIns</p>";
        }
        if($rollbackMessages != ""){
            $migrationMessages .= $rollbackMessages;
        }
    }else{
        $migrationMessages .= "<p class='success'>All migrations have been inserted correctly</p>";
        $migrationMessages .= "<p class='constant'>Elapsed time: $elapsedTime s.</p>";
    }
}else{
    
    if(isset($_GET['execute']) && $_GET['execute'] == 'yes' 
        && isset($_GET['onlyonce']) && isset($_SESSION['onlyOnce']) &&
         $_GET['onlyonce'] != $_SESSION['onlyOnce']){
        
        $migrationMessages .= "<p class='error'>You've executed this yet. Please come back and try it again</p>";
        
    }elseif(!empty($migratesToExecute)){
        
        $onlyOnce = uniqid();
        $_SESSION['onlyOnce'] = $onlyOnce;
        $migrationMessages .= "<p>Execute ? <a href='./migrate.php?execute=yes&onlyonce=$onlyOnce'>Yes</a></p>";
    }else{
        $migrationMessages .= "<p class='success'>There's no migrates to execute</p>";
    }
    
}
$template = str_replace("{{replace_content}}", $migrationMessages, $template);


$template = str_replace("{{replace_footer}}", "Powered by " . Tau::getTauFrameworkGreek(), $template);

echo $template;


function removeMysqlComments($text){
    $lines = explode("\n", $text);
    $resultLines = "";
    foreach($lines as $line){
        $line = trim($line, " ");
        if(!(preg_match('/^--.+$/',$line))) {
            $resultLines .= $line . " ";
        }
    }
    return $resultLines;
}

?>