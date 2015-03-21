<?php
session_start();
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
require_once( __ROOT__ . "/tau/inc/framework/TauSession.php");
require_once( __ROOT__ . "/tau/inc/DataManager.php");
require_once( __ROOT__ . "/tau/inc/elements/TauForm.php");

$lang = Tau::getInstance()->getLang();
$oRender = new PageRender($lang, 'es_ES');
$oReplacer = new Replacer();
$tau = Tau::getInstance();
TauSession::put('name_user','aaron'); //to save images in a/a folder

$email_placeholder = DataManager::getInstance()->getVar("select content from tau_translations where lang='es' and t_group='login.rep' and item='{placeholder_email_user}'; ");

$oReplacer->addFilter("{email_placeholder}", $email_placeholder);

/* Form Creation */
$oForm = new TauForm( "frmTest", APPLICATION_BASE_URL .  "/frmTestAction.php/");

/* 
 * If you are modifying an existing row, you can say which table, field and value to use.
 * If your form relays on data from various tables, you need to set at hand
 * If you need a model from not default db, use the fourth parameter
 */
$oForm->setModelAndRow('tau_translations', 'id_trans', '1');

/* Format A: field names don't match database field names, we need mapping */
//$oForm->setModelMapping( array('textOne' => 'item', 'textTwo' => 'content'));
//$oForm->addInputText("textOne", "ml:3|Ml:12", "Texto uno");
//$oForm->addInputText("textTwo", "ml:3|Ml:12", "Texto dos");

/* Format B: field names matches database field names, we don't need to map */
$oForm->addInputText("item", "ml:3|Ml:12", "Texto uno");
$oForm->addInputText("content", "ml:3|Ml:12", "Texto dos");

/*Even when mapped, we can add other fields not related to this table */
$oForm->addInputText("id_otro", "o", "Otro", LAYOUT, false, 'miValor' );

/* Test rules */
$oForm->addInputText("test_required", "*", "Required", LAYOUT, false, 'algo' );
$oForm->addInputText("test_alphanum", "a", "Alphanum", LAYOUT, false, '123a' );
$oForm->addInputText("test_aex", "aex", "AlphanumExt", LAYOUT, false, '123aÇ_úß' );
$oForm->addInputText("test_email", "e", "Email", LAYOUT, false, 'lucas@lu.com' );
$oForm->addInputText("test_equals_to", "et:test_email", "et test_email", LAYOUT, false, 'lucas@lu.com' );
$oForm->addInputText("test_url", "url", "Url", LAYOUT, false, 'http://tau.lu' );
$oForm->addInputText("test_num", "num", "Num", LAYOUT, false, '123.12' );
$oForm->addInputText("test_int", "int", "Int", LAYOUT, false, '123' );
$oForm->addInputText("test_min", "int|min:12", "Min 12", LAYOUT, false, '12' );
$oForm->addInputText("test_max", "int|max:42", "Max 42", LAYOUT, false, '42' );
$oForm->addInputText("test_min_len", "ml:3", "ml 3", LAYOUT, false, 'abc' );
$oForm->addInputText("test_max_len", "Ml:6", "Ml 6", LAYOUT, false, 'abcdef' );
$oForm->addInputDate("test_date", "dt", "Fecha");
$oForm->addInputTextArea("textareatest", "o|ml:3|Ml:40", "TextArea");
$oForm->addInputCheckBox("check01", "", "CheckSample");

$oForm->addInputRadioButton( 'sex1', "", "Male", false, 'gender', 'M' );
$oForm->addInputRadioButton( 'sex2', "", "Female", false, 'gender', 'F', true );


//array for select options
$opts = array('' => '-- select --', 'es' => 'Español', 'en' => 'Inglés', 'de' => 'Alemán', 
    'it' => 'Italiano' );
$oForm->addInputSelect('sample_select', '*', 'Select', $opts, false, false);

$linkedTo = array(
'db_name' => '',
'table' => 'tau_translations',
'getvalue' => 'id_trans',
'gettext' => 'item',
'searchfield' => 't_group',
'operator' => '=',
'searchvalue' => 'js_validation'
);
$oForm->addInputSelect('sample_linked', '*', 'Select Linked', $opts, 0, 0, 0, $linkedTo);

//Set the text of submit button
$oForm->setSubmitButtonText("Enviar Modificaciones");

$fileImagePath = 'uploads/a/a/aaron/imagesu/tau64_ins.png';
$oForm->addInputFileImage("file01", "*", "File Image", false, false, $fileImagePath);

$oReplacer->addFilter("{replace_frm_test}", $oForm->toString());
//If you want to use the form container of other theme ( container.html ), you can use:
//$oReplacer->addFilter("{replace_frm_test}", $oForm->toString('other_container_theme'));

$oRender->loadFile("templates/default/pages/normalPageTemplate.html", $oReplacer);

echo $oRender->toString();