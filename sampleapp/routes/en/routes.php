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

$urlMap = array(
    '/en\/enter\/?/' => 'controllers/index/index.php',
    '/en\/register\/?/' => 'controllers/general/login.php',
    '/en\/search\/?/' => 'controllers/index/index.php',
    '/en\/test\/locals\-(.*)\/(.*)/' => 'controllers/general/login.php'
);