<?php
session_start();
/**
 * 
 * @abstract tau Single Entry Point
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 17-oct-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

define('__ROOT__', str_replace("\\","/",dirname(dirname(__FILE__))) );

require_once( __ROOT__ . "/tau/inc/config.php");
require_once( __ROOT__ . "/tau/inc/PageRender.php");
require_once( __ROOT__ . "/tau/inc/Replacer.php");
require_once( __ROOT__ . "/tau/Tau.php" );
require_once( __ROOT__ . "/tau/inc/DataManager.php");
require_once( __ROOT__ . "/tau/inc/InputValidator.php");
require_once( __ROOT__ . "/tau/inc/LanguageLoader.php");
require_once( __ROOT__ . "/tau/inc/LogFile.php");
require_once( __ROOT__ . "/tau/inc/TauFriendShip.php");
require_once( __ROOT__ . "/tau/inc/elements/TauForm.php");
require_once( __ROOT__ . "/tau/inc/framework/TauURI.php");
require_once( __ROOT__ . "/tau/inc/framework/TauMessages.php");
require_once( __ROOT__ . "/tau/inc/framework/TauSession.php");
require_once( __ROOT__ . "/tau/inc/framework/TauRequest.php");
require_once( __ROOT__ . "/tau/inc/framework/TauDispatcher.php");
require_once( __ROOT__ . "/tau/inc/framework/TauRouter.php");
require_once( WEB_PATH . "/libs/facebook.php");
require_once( WEB_PATH . "/routes/general.php");
//ToDo: Make InputValidator fully static

//This array will be passed through all process, until the controller,
//So you can stablish something here, and read it in the controller.
$tauContext = array();
$tauContext['help'] = 'This array will be passed to the final controller';

TauURI::parseURI();
TauRequest::init(TauURI::$url, (TauURI::$parameters)? TauURI::$parameters : array());

TauMessages::addMessage("total url parts: ". TauURI::$urlPartsCount, 'notice', 'index');
TauMessages::addNotice("total url parameters: ". TauURI::$parametersCount, 'index');

LanguageLoader::getInstance()->getTranslations('url', APPLICATION_BASE_URL, Tau::getInstance()->getLang());

//Here you have a chance to alter the controller output,
//and represents the last output of the application ( unless debug logging )
$output = TauRouter::route($urlMap, $tauContext);

//Sending headers and cookies here, to not break the execution order
TauResponse::sendHeadersAndCookies();

//Finally, echoing the page
echo $output; 

if(VERBOSE_MODE){
    TauMessages::addNotice("MAX MEM: " . round(memory_get_peak_usage(true)/1024,0) . " KB ", "indexFrontController");
    echo TauMessages::getAllMessagesHtml();
}

function __autoload($class){
    global $autoloadPaths;
    $inc = false;
    require_once(__ROOT__ . '/tau/inc/framework/TauAutoload.php');
    $inc = new TauAutoload($class, $autoloadPaths);
}
?>


