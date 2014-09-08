<?php

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
$group = tau_get_group($file);
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


foreach($matches as $match){
   // echo "<p>Reading match $match<br/>";
    foreach($match as $coincidence){
        
        $formTable .= "<tr>";
        $tokens[$coincidence] = tau_tokenizer($file, $coincidence);
        $formTable .= "<td>" . $tokens[$coincidence] . "</td>";
        $text_id = 0;
        foreach($allowedLangs as $lang){
            $text_id++;
            $class=" newField";
            $currentContent = $tokensFound[$lang][$tokens[$coincidence]];
            if($currentContent){
               $class = " notNewField"; 
            }
            $identifier = $tokens[$coincidence] . "-$text_id-$lang";
            $formTable .= "<td><textarea cols='25' rows='2' id='$identifier' class='inputs$class' name='$identifier' >".$currentContent."</textarea></td>";
            
        }
        //$myMatches .= "<p>match: $coincidence will translate to <span class='success'>{{" . $tokens[$coincidence]  ."}}</span> and  {$tokens[$coincidence]} in database</p>";
        /*
        if($tokens[$coincidence] ==  substr($coincidence, 2, strlen($coincidence) - 4)){
            //The token was prefixed, and automated already by tau framework
            $myMatches .= "<p>$coincidence is yet in database </p>";
        }*/
        $formTable .= "</tr>";
    }
    
}
$formTable .= "</table>";
$myMatches .= $formTable;

$template = str_replace("{{replace_footer}}", "Powered by " . Tau::getTauFrameworkGreek(), $template);


$template = str_replace("{{replace_content}}", $myMatches, $template);

echo $template;

function tau_tokenizer($full_file_path, $token){
    
    $ff = $full_file_path;
    $token = substr($token, 2, strlen($token) - 4);
    
    $sha1 = substr(sha1($ff), 0, 7);
    $tau_prefix = "tau_" . $sha1 . "_";
    
    if(substr($token,0,12) == $tau_prefix){
        return $token;
    }
    
    return "tau_" . $sha1 . "_" . $token;
}

function tau_get_group($full_file_path){
    
    $ff = $full_file_path;
    
    
    $sha1 = substr(sha1($ff), 0, 7);
    return "tau_" . $sha1;
    
}

?>