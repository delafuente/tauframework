<?php

/**
 * 
 * @abstract tau
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 28-sep-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

define('__ROOT__', str_replace("\\","/",dirname(dirname(__FILE__))) );

require_once( __ROOT__ . "/tau/inc/config.php");
require_once( __ROOT__ . "/tau/inc/PageRender.php");
require_once( __ROOT__ . "/tau/Tau.php" );
require_once( __ROOT__ . "/tau/inc/DataManager.php");
require_once( __ROOT__ . "/tau/inc/InputValidator.php");

$lang = 'es';
$oRender = new PageRender($lang, 'es_ES');
$oReplacer = new Replacer();
$oInputValidator = new InputValidator($_POST);
$filteredPost = $oInputValidator->getCleanArray();

//$email_placeholder = DataManager::getInstance()->getVar("select content from tau_translations where lang='es' and t_group='login.rep' and item='{placeholder_email_user}'; ");

$oReplacer->addFilter("{email_placeholder}", "received textOne: " . $filteredPost['textOne'] . " textTwo: " . $filteredPost['textTwo']);


$oReplacer->addFilter("{replace_frm_test}", "");

$oRender->loadFile("templates/default/pages/normalPageTemplate.html", $oReplacer);

echo $oRender->toString();
?>
