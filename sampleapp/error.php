<?php
session_start();
/**
 * 
 * @abstract Main error page sample, some errors will redirect here
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 11-oct-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

echo "<p>" . $_SESSION['last_error'] . "</p>";
?>
