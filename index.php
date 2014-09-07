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

//define('__ROOT__', dirname(dirname(__FILE__)) );

?>
<head>
    <link rel="stylesheet" type="text/css" href="tau/install/install.css">
</head>
<body><div class="main">
<?php

require_once( "/tau/Tau.php" );
require_once( "/tau/inc/config.php" );

if(APPLICATION_ENVIRONMENT != 'local'){
    echo '<p>Cannot access this file</p>';
    die();
}

$tau = Tau::getInstance();

echo "<h3>Tau Framework working: </h3><br/>";

echo "<p>Current environment: <span class='lightgreen'>" . $tau->getEnvironment() . "</span></p>";

$all_constants = get_defined_constants(true);

echo "<p>All user defined constants: </p>";


    $tf = "<span class='tf'>";
    $tfe = "</span>";

foreach($all_constants['user'] as $key => $val){    
    if($val === false){ $val = $tf."false".$tfe; }elseif($val === true){ $val = $tf."true".$tfe; }
    echo "<span class='constant'>$key </span> =&gt; $val <br/>";
}

?>
</div></body>