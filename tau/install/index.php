<?php

/**
 * 
 * @abstract Start point of installation
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 05-jul-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
define('__ROOT__', str_replace("\\","/",dirname(dirname(__FILE__))) );

?>
<head>
    <link rel="icon" type="image/png" href="/tau32g.png" sizes="32x32">
    <link rel="icon" type="image/png" href="/tau64g.png" sizes="64x64">
<style>
    body{
        color: rgb(90, 90, 90);
        display: inline;
        font-family: monospace, serif;
        font-size: 14px;
        height: auto;
        line-height: 14.399999618530273px;
        white-space: pre-wrap;
        width: auto;
    }
    
    .success{ color:#0a0;}
    .red{ color:#f00; }
    .main{
        margin:0px;
        padding:2px;
        margin-left: 25px;
        margin-top: 25px;
    }
    label{ cursor:pointer; }
    h3,h4{ margin:0px;}
    .constant{ color:#a901db; }
</style>
</head>
<body>
<div class="main">
    <h3>Welcome to &tau;&alpha;&upsilon; installation</h3>
   
<?php
//Initial testing
if( !file_exists(__ROOT__ . "/../tau/inc/config.php" ) || !file_exists(__ROOT__ . "/../tau/inc/settings.php")){
    echo "<p class='red'> tau/inc/config.php and tau/inc/settings.php files must exist <br/>( you can rename config_sample.php and settings_sample.php )".
            " and fill them with your configuration information to proceed with installation.</p>";
    terminate();
}else{
    echo "<p class='success'> check: [OK] config.php and settings.php files exist</p>";
}

if( file_exists(__ROOT__ . "/../tau/install/app_installed" )){
    echo "<p class='red'> Application yet installed. You can remove or rename file tau/install/app_installed, and try again, but with unexpected behavior.</p>";
    terminate();
}

?>
    <h3>Select the features that will be installed: </h3>
    <h4>Modules</h4>
    <form name="frmFeaturesTau" action="./processInstall.php" method="post" enctype="multipart/form-data" >   
    <input id="ch_friends" type="checkbox" name="module_tau_friendship" /> <label for="ch_friends">Friendship system to allow users to be friends of others, as in other social media software</label> <br/>
    <input id="ch_gallery" type="checkbox" name="module_tau_gallery" /> <label for="ch_gallery">Photo galleries system, to allow users to create public, private ( only friends ) 
        or password protected galleries. ( requires friendship ) </label><br/>
    <input id="ch_cache" type="checkbox" name="module_tau_cache" /> <label for="ch_cache"> Cache system to allow caching some non real-time pages </label> <br/>
    <input id="ch_uploads" type="checkbox" name="module_tau_uploads" /> <label for="ch_uploads"> Uploads system ( it's in core, but module will create folder structure in <span class="constant">TAU_UPLOADS_PATH</span> ) </label> <br/>
        
        
    <input type="submit" name="btnSubmit" value="Install &tau;&alpha;&upsilon; Framework" />    
    </form>
    
<?php    
terminate();
?>

<?php

function terminate(){
    echo "<hr/><p>Powered by &tau;&alpha;&upsilon; &phi;&rho;&alpha;&mu;&epsilon;&#989;o&rho;&kappa;.</p></div>";
        die();
}

?>
</body>