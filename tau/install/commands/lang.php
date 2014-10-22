<?php
/**
 * 
 * @abstract
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 21-oct-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

define('__ROOT__', str_replace("\\","/",dirname(dirname(__FILE__))) );

require_once( __ROOT__ . "/../../tau/inc/config.php");

if(APPLICATION_ENVIRONMENT != 'local'){
    echo '<p>Cannot access this file</p>';
    die();
}

echo "<h2>Choose the file to edit translations</h2>";
/*
$path = realpath(WEB_PATH);
$count = 0;

$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
foreach($objects as $name => $object){
    $count++;
    $external = str_replace(WEB_PATH, "", $name);
    echo "$count - <a href='languageCompleter.php?file=$name'>$external</a> <br/>";
}
*/
$template = file_get_contents("finder.html");
$template = str_replace("{rep_base_url}", APPLICATION_BASE_URL . "/", $template);
//$template = str_replace("{rep_basename}", WEB_PATH . "/", $template);
$template = str_replace("{rep_dest}", WEB_PATH . "/", $template);
$template = str_replace("{rep_lang_completer}", TAU_BASE_URL ."/install/commands/languageCompleter.php?file=", $template);



echo $template;
?>

