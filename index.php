<?php

/**
 * 
 * @abstract Tau index
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 06-jul-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

define('__ROOT__', dirname(dirname(__FILE__)) );

require_once( __ROOT__ . "/tau/Tau.php" );
require_once( __ROOT__ . "/tau/inc/config.php" );

$tau = new Tau();

echo "<h3>Tau Framework working: </h3><br/>";

echo "<p>Current environment: " . $tau->getEnvironment() . "</p>";

$all_constants = get_defined_constants(true);

print_r($all_constants);

?>
