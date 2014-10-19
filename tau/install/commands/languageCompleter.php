<?php
session_start();
/**
 * 
 * @abstract tau
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 22-jul-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

define('__ROOT__', str_replace("\\","/",dirname(dirname(__FILE__))) );

require_once( __ROOT__ . "/../../tau/inc/config.php");
require_once( __ROOT__ . "/../../tau/inc/InputValidator.php");
require_once( __ROOT__ . "/../../tau/Tau.php" );
require_once( __ROOT__ . "/../../tau/inc/DataManager.php");
require_once( __ROOT__ . "/../../tau/inc/framework/TauResponse.php");
require_once( __ROOT__ . "/../../tau/inc/framework/TauSession.php");

if(APPLICATION_ENVIRONMENT != 'local'){
    echo '<p>Cannot access this file</p>';
    die();
}

$tau = Tau::getInstance();
$db = DataManager::getInstance();
$template = file_get_contents("languageCompleter_template.html");


$inputValidator = new InputValidator($_REQUEST);
$cleanInput = $inputValidator->getCleanArray();
$file = $cleanInput['file'];
$fileRelative = str_replace(APPLICATION_PATH, "", $file);


$template = str_replace("{{replace_full_filename}}", $file, $template);

if(!file_exists($file) || !isset($_GET['file'])){
    $template = str_replace("{{replace_content}}", "<p class='red'>File '" . $file . "' does not exist</p>", $template);
    $template = str_replace("{{replace_footer}}", "Powered by " . Tau::getTauFrameworkGreek(), $template);
    echo $template;
    die();
}

$file_contents = file_get_contents($file);

//Found tokens
preg_match_all("/\{\{[^\}]*\}\}/", $file_contents, $matches);
$tokens = array();
$tokensFound = array();
$allowedLangs = explode(",",ALLOWED_LANGS);
$totLangs = count($allowedLangs);
$group = Tau::tau_get_group($fileRelative);
$myMatches = "";
$formTable = "<table><thead><tr><th></th>";

if($totLangs < 1 || ALLOWED_LANGS == ""){
    $template = str_replace("{{replace_content}}", "<p class='red'>You have no allowed langs in settings.php ALLOWED_LANGS constant</p>", $template);
    $template = str_replace("{{replace_footer}}", "Powered by " . Tau::getTauFrameworkGreek(), $template);
}

foreach($allowedLangs as $lang){
    $formTable .= "<th>$lang</th>";
}

$formTable.= "</tr>";
$myMatches .= "<p>Searching for group <span id='t_group' class='success'>$group</span></p>";

$currentTranslations = $db->getResults("select * from tau_translations where t_group='" . $group . "';");

foreach($currentTranslations as $key => $trans){
    //$myMatches .= "<p class='success'> found register for " . $trans['lang'] . " item:" . $trans['item'] . " translation:" . $trans['content'] . "</p>";
    $tokensFound[$trans['lang']][$trans['item']] = $trans['content'];
}
$_SESSION['tokensFound'] = $tokensFound;

foreach($matches as $match){
   // echo "<p>Reading match $match<br/>";
    foreach($match as $coincidence){
        
        $formTable .= "<tr>";
        $tokens[$coincidence] = Tau::tau_tokenizer($fileRelative, $coincidence);
        $formTable .= "<td>" . $tokens[$coincidence] . "</td>";
        $text_id = 0;
        foreach($allowedLangs as $lang){
            $text_id++;
            $class=" newField";
            @$currentContent = $tokensFound[$lang][$tokens[$coincidence]];
            if($currentContent){
               $class = " notNewField"; 
            }
            $identifier = $tokens[$coincidence] . "-$text_id-$lang";
            $formTable .= "<td><textarea cols='25' rows='2' id='$identifier' class='inputs$class' name='$identifier' >".$currentContent."</textarea></td>";
            
        }
        
        $formTable .= "</tr>";
    }
    
}
$formTable .= "</table>";
$myMatches .= $formTable;

$template = str_replace("{{replace_footer}}", "Powered by " . Tau::getTauFrameworkGreek(), $template);


$template = str_replace("{{replace_content}}", $myMatches, $template);

echo $template;

?>