<?php

/**
 * 
 * @abstract
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 23-oct-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

//$oRender = new PageRender('es', 'es_ES');
$lang = Tau::getInstance()->getLang();
TauResponse::addHeader($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");

switch($lang){
    case 'es': echo "<h1>404 Página no encontrada</h1>"; break;
    case 'en': echo "<h1>404 Not found</h1>"; break;
    default: echo "<h1>404 Page Not found</h1>";
}