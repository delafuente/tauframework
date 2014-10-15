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
require_once( __ROOT__ . "/tau/inc/elements/TauForm.php");

$lang = 'es';
$oRender = new PageRender($lang, 'es_ES');
$oReplacer = new Replacer();
$tau = Tau::getInstance();

$email_placeholder = DataManager::getInstance()->getVar("select content from tau_translations where lang='es' and t_group='login.rep' and item='{placeholder_email_user}'; ");

$oReplacer->addFilter("{email_placeholder}", $email_placeholder);

/* Form Creation */
$oForm = new TauForm("frmTest",APPLICATION_BASE_URL .  "/frmTestAction/");
//$oForm = new TauForm("frmTest",APPLICATION_BASE_URL . "/" . APP . "/frmTestAction/", 'test');

//The following line is only for 'edit' or 'modify' row actions, with existing data
$oForm->setModelAndRow('tau_translations', 'id_trans', '1');

/* Format A: field names don't match database field names, we need mapping */
//$oForm->setModelMapping( array('textOne' => 'item', 'textTwo' => 'content'));
//$oForm->addInputText("textOne", "ml:3|Ml:12", "Texto uno");
//$oForm->addInputText("textTwo", "ml:3|Ml:12", "Texto dos");

/* Format B: field names matches database field names, we don't need to map */
$oForm->addInputText("item", "ml:3|Ml:12", "Texto uno");
$oForm->addInputText("content", "ml:3|Ml:12", "Texto dos");

$oReplacer->addFilter("{replace_frm_test}", $oForm->toString());

$oRender->loadFile("templates/default/pages/normalPageTemplate.html", $oReplacer);

echo $oRender->toString();