<?php

/**
 * 
 * @abstract generic rules for url routing, for all languages
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 25-oct-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

$urlMap = array(
    '/^\/$/' => 'controllers/index/index.php',
    //'/([a-z]{2})/' => 'controllers/index/index.php',
    '/404/' => 'controllers/general/404.php',
    '/error/' => 'controllers/general/error.php',
    '/media\/search\-(.*)\/(.*)/' => 'controllers/general/login.php',
    '/frmtest/' => 'controllers/test/frmTest.php',
);