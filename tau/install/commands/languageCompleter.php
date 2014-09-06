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
}

$file_contents = file_get_contents($file);

//Found tokens
preg_match_all("/\{\{[^\}]*\}\}/", $file_contents, $matches);
$tokens = array();

foreach($matches as $match){
   // echo "<p>Reading match $match<br/>";
    foreach($match as $coincidence){
        
        $tokens[$coincidence] = tau_tokenizer($file, $coincidence);
        
        $myMatches .= "<p>match: $coincidence will translate to <span class='success'>{{" . $tokens[$coincidence]  .
                "}}</span> and  {$tokens[$coincidence]} in database</p>";
        
        if($tokens[$coincidence] ==  substr($coincidence, 2, strlen($coincidence) - 4)){
            //The token was prefixed, and automated already by tau framework
            $myMatches .= "<p>$coincidence is yet in database </p>";
        }
        
    }
    
}
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
?>