<?php
/**
 * Rename this file to config.php
 * @abstract This file points to settings.php
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 02-feb-2011
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
require_once(dirname(__FILE__). DIRECTORY_SEPARATOR . "../Tau.php");

$tau_base_path = dirname(dirname(__FILE__));

/**
 * You need a line on your VirtualHost like:
 * SetEnv APPLICATION_ENVIRONMENT local
 * If you don't set, production config is loaded
 */
define('APPLICATION_ENVIRONMENT',Tau::getEnv('APPLICATION_ENVIRONMENT'));


if( APPLICATION_ENVIRONMENT == 'local' ){
    //edit
    define('APP','sampleapp');  
    define('APP_SLUG','sampleapp'); 
    define('TLD','com');
    define('LANG','es');
    define('BASE','sampleapp');
    define('LAYOUT','standard');
    define('ERROR_REPORTING_STATUS',true);
    //edit end
    define('SETTINGS_FILE', $tau_base_path . '/inc/settings.php');
    setEnvironment('LOCAL_WITH_LOCALHOST');
    
}else if( APPLICATION_ENVIRONMENT == 'pre' ){
    //edit
    define('APP','sampleapp');  
    define('APP_SLUG','sampleapp'); 
    define('TLD','com');
    define('LANG','es');
    define('BASE','sampleapp');
    define('LAYOUT','standard');
    define('ERROR_REPORTING_STATUS',true);
    //edit end
    define('SETTINGS_FILE', $tau_base_path . '/inc/settings.php');
    setEnvironment('PRE_PRODUCTION_ENVIRONMENT');
    
}else{
    //Production config
    //edit
    define('APP','sampleapp');  
    define('APP_SLUG','sampleapp'); 
    define('TLD','com');
    define('LANG','es');
    define('BASE','sampleapp');
    define('LAYOUT','standard');
    define('ERROR_REPORTING_STATUS',false);
    //edit end
    define('SETTINGS_FILE', $tau_base_path . '/inc/settings.php');
    setEnvironment('PRODUCTION_ENVIRONMENT');
    
}

ini_set('log_errors', true);
ini_set('html_errors', false);
ini_set('error_log', LOG_PATH . '/php_error_' . strtolower(ENV_NAME));
ini_set('display_errors', false);

define('LOCAL_DRIVE',"C"); //For Windows users

date_default_timezone_set("Europe/Madrid");

$myapp = APP_SLUG;
define('MYAPP',APP_SLUG . "/");
$template_slug = APP_SLUG . "/";

require_once(SETTINGS_FILE);


function setEnvironment($env) {
    $envs = array('PRO' => 'PRODUCTION_ENVIRONMENT','PRE' => 'PRE_PRODUCTION_ENVIRONMENT','DEV' => 'DEVELOPMENT_ENVIRONMENT',
        'LOCAL' => 'LOCAL_WITH_LOCALHOST','LAN' => 'LOCAL_WITH_LAN_ACCESS','MAC' => 'LOCAL_MAC');
    
    global $whereami;
    
    foreach ($envs as $key => $value) {
        if($value == $env){
            define($value,true);
            define('ENV_NAME',$key);
            $whereami = $value;
        }else{
            define($value,false);
        }
    }
}

function sendSimpleMail($from,$to,$subject,$message,$headers=false,$replyTo=false){

    if(!$replyTo){
        $replyTo = $from;
    }

    $additional_headers = "From: " . $from . " \n" .
    "Reply-To: " . $replyTo . " \n" .
    "X-Mailer: PHP/" . phpversion();
    $additional_headers .= 'Content-type: text/html; charset=utf-8' . "\n";

    if($headers){

    $additional_headers .= $headers;

    }
    $additional_headers .="\n";

    return mail($to,$subject,$message,$additional_headers);

}

function log_error($message){
     $bt =  debug_backtrace();
     $LOG_ACTIVE = true;
     $USE_FULL_PATH = false;

    if(!$USE_FULL_PATH){
        $arr = explode("/",$bt[0]['file']);
        $file = end($arr);
    }else{
        $file = $bt[0]['file'];
    }

    if($LOG_ACTIVE){
        error_log(session_id() . " <" . $file . " @ " . $bt[0]['line'] . "> " . $message);
    }

}

function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

function filterCityNameUrl($city){
    $city = str_replace(" ","-",$city);
    $city = strtolower($city);
    return $city;
}

function replaceLangUrl($newLang){
    return APPLICATION_BASE_URL . "/" . $newLang . "/";
}

?>
