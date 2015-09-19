<?php

/**
 * 
 * @abstract tau
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 05-jul-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

//define('__ROOT__', str_replace("\\","/",dirname(dirname(__FILE__))) );
//
//require_once( __ROOT__ . "/tau/inc/config.php");
//require_once( __ROOT__ . "/tau/inc/PageRender.php");
//require_once( __ROOT__ . "/tau/Tau.php" );
//require_once( __ROOT__ . "/tau/inc/DataManager.php");
echo "<h1>INSIDE Controller : /controllers/index/index.php</h1>";

$tau = Tau::getInstance();

echo "<h3>" . Tau::getTauGreek() . " Framework working: </h3><br/>";

echo "<p>Current environment: " . $tau->getEnvironment() . "</p>";

echo "<p style='color:f0f'>We're inside the TauDispatcher::dispatch(path, params, context) method,".
"so we can pass any context variable to the controller in that method signature</p>";
echo "<p>Example: Accessing TauDispatcher self::\$test:  " . self::$test . "</p>";

echo "<p style='color:f0f'>But we cannot access any other variable in front index,".
        "unless passed through the \$tauContext array</p>";
echo "<p>reading a variable from TauContext: " . $tauContext['help'] . "</p>";

$all_constants = get_defined_constants(true);
//print_r($all_constants);

$db1 = DataManager::getInstance();

echo "<p>db1 name: " . $db1->getDataBaseName() . "</p>";
echo "<code>db1: " . $db1->getVar("select content from tau_translations where lang='es' and item='LU_NUM_REGISTERS_FOUND' limit 1;") . "</code>";

$db2 = DataManager::getInstance("test_sampleapp");
echo "<p>db2 name: " . $db2->getDataBaseName() . "</p>";
echo "<code>db2: " . $db2->getVar("select content from tau_translations where lang='es' and item='LU_INIT_SESSION' limit 1;;") . "</code>";

echo "<p>db1 name: " . $db1->getDataBaseName() . "</p>";

$db3 = DataManager::getInstance();
echo "<h4> After db3 = DataManager::getInstance()</h4>";

echo "<p>db3 name: " . $db3->getDataBaseName() . "</p>";
echo "<code>db3: " . $db3->getVar("select content from tau_translations where lang='es' and item='LU_FORGOT_PASS' limit 1;") . "</code>";

echo "<p>db1 name: " . $db1->getDataBaseName() . "</p>";
echo "<code>db1: " . $db1->getVar("select content from tau_translations where lang='en' and item='LU_NUM_REGISTERS_FOUND' limit 1;") . "</code>";


echo "<p>db2 name: " . $db2->getDataBaseName() . "</p>";
echo "<code>db2: " . $db2->getVar("select content from tau_translations where lang='en' and item='LU_NUM_REGISTERS_FOUND' limit 1;") . "</code>";


$oRender = new PageRender('es', 'es_ES');

$oRender->toString();
//echo "<br/>". __LINE__ . " here " . time() . " <br/>";
//$db1->close();
//$db2->close();
//$db3->close();

?>
<hr/>
<br/>
<img src="<?php echo APPLICATION_BASE_URL; ?>/images/asturias.jpg"/>
<?php
echo "<hr/>";
echo "<p>Powered by " . Tau::getTauFrameworkGreek() . ".</p>";

echo "<h1>END OF INDEX.PHP</h1>";
?>
