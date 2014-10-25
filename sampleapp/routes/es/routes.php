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
    '/es\/entrar\/?/' => 'controllers/general/login.php',
    '/es\/registro\/?/' => 'controllers/index/index.php',
    '/es\/buscar\/?/' => 'controllers/index/index.php',
    '/es\/prueba\/locales\-(.*)\/(.*)/' => 'controllers/index/login.php'
);