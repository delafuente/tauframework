<?php

/**
 * @abstract creates the folder structure of uploads: a/a a/b b/a..b/z etc
 * @author Lucas de la Fuente
 * @version 08-dic-2012
 * @package tau
 * @copyright Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

if (APPLICATION_INSTALLED || file_exists("app_installed")) {
    echo "<p class='red'>Application yet installed.</p>";
    return;
}

$logger = new LogFile(
                true, "installMakeUploadsFolders.txt", true); //see params to set append or overwrite, and change file

$folderCharsList = "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,0,1,2,3,4,5,6,7,8,9";
$folderChars = explode(",", $folderCharsList);
if(!isset($_GET['install-path'])){
    $installIn = TAU_UPLOADS_PATH;
}else{
    $installIn = $_GET['install-path'];
}
if(file_exists(TAU_UPLOADS_PATH )){
    echo "<p class='red'>" . TAU_UPLOADS_PATH  . " Yet exists. Please delete the folder if you want a clean installation, or maintain the one you have now if desired.</p>";
    return;
}
if(TAU_UPLOADS_PATH == "/path/to/your/app/uploads/"){
    echo "<p class='red'>" . TAU_UPLOADS_PATH  . " is the default value. You must specify one folder of your application to create ".
            "subfolders uploads structure in inc/settings.php TAU_UPLOADS_PATH constant.</p>";
    return;
}
echo "<p>Install path: " . TAU_UPLOADS_PATH . "</p>";
if(!file_exists(TAU_UPLOADS_PATH )){
    createDir(TAU_UPLOADS_PATH , $logger);
}
if(!file_exists(TAU_UPLOADS_PATH )){
    echo "<p class='red'>" . TAU_UPLOADS_PATH  . " doesn't exist, and Tau can't also create. Check permissions over the folder</p>";
    return;
}
$counter = 0;
foreach ($folderChars as $firstLevel) {
    $counter++;
    if($counter == 1 && file_exists($installIn . $firstLevel)){
        echo "<p class='red'>Folder structure yet installed, at least 'a' directory</p>";
        return;
    }
    $res = createDir($installIn . $firstLevel, $logger);
    if ($res == 'OK') {
        //echo "<p>$counter created folder: " . $installIn . $firstLevel . "</p>";
    } else {
        echo "<p style='color:#f00'>$counter ERROR creating $res </p>";
    }
}

foreach ($folderChars as $firstLevel) {
    foreach ($folderChars as $secondLevel) {
        $counter++;
        $res = createDir($installIn . $firstLevel . "/" . $secondLevel, $logger);
        if ($res == 'OK') {
            //echo "<p>$counter created folder: " . $installIn . $firstLevel . "/" . $secondLevel . "</p>";
        } else {
            echo "<p style='color:#f00'>$counter ERROR creating $res </p>";
        }
    }
}

echo "<p class='success'>End creating uploads folder structure.</p>";

function createDir($final_image_path, &$logger) {

    if (mkdir($final_image_path, 0755)) {
        $logger->writeToFile($final_image_path . "/" . "index.php", "<?php \n echo 'nothing to see here';\n ?>", 'w');
        chmod($final_image_path . "/index.php", 0755);
        chmod($final_image_path, 0755);
        return "OK";
    } else {
        $strErr = $final_image_path;
        return utf8_encode($strErr);
    }
}

?>
