<?php
session_start();
/**
 * 
 * @abstract Class to automate uploaded files handling and securing
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 08-jul-2010
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */


require_once("config.php");
require_once("LogFile.php");

class TauFile{
    
    private $output; // Execution information, for debugging
    private $logger; // LogFile object, to write log info
    private $lastFileNamePath;
    private $fileNameModeUID;
    private $thumbAutoSize;
    private $makeInternalFolders; //If true, will make internal folders as 2012_jun to save files
    private $deleteMainFileAfterThumbCreation;
    private $directorySeparator;

/**
 * Creates a new LuFile object.
 * @param boolean $fileNameModeUID If true ( default ), filename will be uniqid()
 */
function __construct($fileNameModeUID=true){

    $this->fileNameModeUID = $fileNameModeUID;
    $this->thumbAutoSize = false;
    $this->makeInternalFolders = false;
    $this->deleteMainFileAfterThumbCreation = false;
    $this->directorySeparator = "/";
    
    $this->logger = new LogFile(
        DEBUG_MODE,"file.php",true); //see params to set append or overwrite, and change file
		
    $this->logger->put("<p>Instance LuFile " . date("Y-m-d h:i:s",time()) . "</p>");
    
}
/**
 * Must be called before saveImageFile(). Will force the creation of year_month folder
 * to save files, like 2012_jan, 2005_jun etc, depending on current date.
 */
public function addInternalFolders(){
    $this->makeInternalFolders = true;
}
/**
 * If this is set to true, the main image will be deleted after thumb creation.
 * @param boolean $boolean True or false.
 */
public function setDeleteMainFileAfterThumbCreation($boolean=true){
    $this->deleteMainFileAfterThumbCreation = $boolean;
}
/**
 * Will set the bigger size of the thumb to this size, and
 * the createThumb() method will create a image stretched trying to maintain
 * aspect, vertical or horizontal.
 * @param int $maxSizeBiggerSide The new length of the bigger size of the image
 */
public function setThumbAutoSize($maxSizeBiggerSide){
    $this->thumbAutoSize = $maxSizeBiggerSide;
}
/**
 * Set the fileNameModeUID to true or false.
 * @param boolean $boolean If true, the image name will be uniqid()
 */
public function setFileNameModeUID($boolean=true){
    $this->fileNameModeUID = $boolean;
}
/**
 * Return the output member, with execution information. For debugging.
 */
public function getOutput(){
    return $this->output;
}
public function getFileNamePath(){
    return $this->lastFileNamePath;
}
/**
* Save uploaded file to current user uploads folder, and assumes /imagesu and /thumbs folders
* exist in user uploads folder.If LU_MAKE_THUMBS is true will make a thumb. Also make a Year_monthNameAbbreviated folder
* inside each one( i.e: 2010_june ). This function will check for allowed extensions, mime types, exif, not executable extensions 
* present in path ( image.php.jpg will be rejected ), file size, and max dimensions ( width and height ). See the
* inc/settings.php file for more info.
* @param String     nameFileInput   The file input name
* @param String     username        The user nick, if not specified will try to use $_SESSION['name_user'].
* @param Boolean    overwrite       If true, will overwrite file if exists
* @param Boolean    makeThumb       If true, and LU_MAKE_THUMBS constant true, will make a thumb
* @param Integer    thumbWidth      The thumb width in pixeles
* @param Integer    thumbHeight     The thumb height in pixeles
* @param Integer    thumbQuality    The thumb quality, from 1 to 99. For png will be automatically rounded to 0-9 (see createThumb())
* @version 2010/06/17
* 
*/

function saveImageFile($nameFileInput,
            $username=false,
            $overwrite=false,
            $makeThumb=true,
            $thumbWidth=LU_THUMBS_WIDTH,
            $thumbHeight=LU_THUMBS_HEIGHT,
            $thumbQuality=LU_THUMBS_QUALITY){
    
    
    $nameFile = $nameFileInput;
    if($username){
        $usname = $username;
    }else{
        $usname = strtolower(TauSession::get('name_user'));
    }
    
    
    $usname = str_replace(array(" ",".","-","+","_"),"",$usname);
            
            $usname = preg_replace( 
                         array("/\s+/", "/[^-\.\w]+/"), 
                         array("_", ""), 
                         trim($usname));
            
    $usname = preg_replace("/[^a-zA-Z0-9]/", "", $usname);
    
    $preNickA = substr($usname, 0, 1);
    $preNickB = substr($usname, 1, 1);
    $preNick = $preNickA . "/" . $preNickB;
    $usname = strtolower($usname);
    
    $destination_path =  TAU_UPLOADS_PATH  . $preNick . $this->directorySeparator . $usname . $this->directorySeparator;
    $month = @date("F",time());
    $year = @date("Y",time());
    $relative_path_part =  $year . "_" . strtolower($month);
    $this->logger->put("\n\nDestination path: $destination_path\nusname=$usname \npath_sep: " . $this->directorySeparator);
    if($this->makeInternalFolders){
        $final_image_path = $destination_path . "imagesu" . $this->directorySeparator . $relative_path_part . $this->directorySeparator;
        $final_thumb_path = $destination_path . "thumbs". $this->directorySeparator . $relative_path_part . $this->directorySeparator;
    }else{
        $final_image_path = $destination_path . "imagesu" . $this->directorySeparator;
        $final_thumb_path = $destination_path . "thumbs". $this->directorySeparator;
    }
    
    
    $this->output ="<p>File uploaded:</p>";
    $errorCode = false;
    
    
    $allowEx = explode(",",LU_IMAGE_ALLOWED_EXTENSIONS);
    $allowMime = explode(",",LU_IMAGE_ALLOWED_MIME_TYPE);
    
    $this->logger->put("\nexts: " . LU_IMAGE_ALLOWED_EXTENSIONS . "\n");
    $allowExif = array();
    
    $allowExif[0] = IMAGETYPE_JPEG;
    $allowExif[1] = IMAGETYPE_GIF;
    $allowExif[2] = IMAGETYPE_PNG;
    
    //Check that we have a file
    if((!empty($_FILES[$nameFile])) && ($_FILES[$nameFile]['error'] == 0)) {
      
      $filename = basename($_FILES[$nameFile]['name']);
      $this->logger->put("\nfilename: $filename\n");

      if ($this->checkExtension($filename,$allowEx) && 
        $this->checkMime($_FILES[$nameFile]["type"],$allowMime)  && 
        $_FILES[$nameFile]["size"] < LU_MAX_IMAGE_SIZE && 
        $filename != "." && $filename !=".." && $filename != "" &&
        $this->checkExif($_FILES[$nameFile]['tmp_name'] ,$allowExif) && 
        $this->checkExec($filename) && 
        $this->checkDimensions($filename)  ) {
            $this->logger->put("Passed constraints\n");    
          //  sanitize file name
          //       - change some letters by others
            //     - remove extra spaces/convert to _, 
            //     - remove non 0-9a-Z._- characters, 
            //     - remove leading/trailing spaces

          
          /** this will be returned to save in DB */
          if($this->fileNameModeUID){
                $safe_filename = uniqid() . "." . strtolower(end(explode(".",$filename))) ;
            }else{
                $changeThisLetters=explode(",","Ñ,ñ,á,é,í,ó,ú,Á,É,Í,Ó,Ú,ü,Ü,ç,Ç, ");
                $changeForThisLetters = explode(",","N,n,a,e,i,o,u,A,E,I,O,U,u,U,c,C,_");
                $safe_filename = str_replace($changeThisLetters,$changeForThisLetters,$filename);

                $safe_filename = preg_replace(
                         array("/\s+/", "/[^-\.\w]+/"),
                         array("_", ""),
                         trim($safe_filename));
            }
          if($this->makeInternalFolders){
            $save_to_db_name = $preNick . $this->directorySeparator . $usname . $this->directorySeparator . "imagesu" . $this->directorySeparator .
                    $relative_path_part . $this->directorySeparator . $safe_filename;
          }else{
            $save_to_db_name = $preNick . $this->directorySeparator . $usname . $this->directorySeparator . "imagesu" . $this->directorySeparator .
                     $safe_filename;
          }
          

          $this->lastFileNamePath = $save_to_db_name;
          //Determine the path to which we want to save this file
          $newname =  $final_image_path . $safe_filename;
          $newname_thumb = $final_thumb_path .  $safe_filename;
          
          //Check if the file with the same name already exists on the server
          if (!file_exists($newname) || $overwrite) {
            //Attempt to move the uploaded file to it's new place
            
                if(!file_exists($final_image_path)){
                    $this->logger->put("\nTrying to create " . $final_image_path);
                    //security check
                    if($final_image_path == APPLICATION_PATH || $final_image_path == (APPLICATION_PATH . $this->directorySeparator)){
                        
                    $this->output .= "Trying to overwrite main index.php - <p>folder final_image_path: $final_image_path</p>";
                    $this->logger->put("Trying to overwrite main index.php - <p>folder final_image_path: $final_image_path</p>");
                    $errMsg = "Trying to overwrite main folder";
                    return  utf8_encode("<p>" . $errMsg . "</p>");
                    }
                    if(mkdir($final_image_path,0755)){
                        //You can avoid following two lines with Options -Indexes in htaccess or VirtualHost configuration
                        $this->logger->writeToFile($final_image_path . "index.php","<?php \n echo 'nothing to see here';\n ?>",'w');
                        chmod($final_image_path . "index.php",0755);
                        chmod($final_image_path,0755);
                    }else{
                        $strErr = "<p>Cannot create $final_image_path</p>";
                        $this->logger->put(utf8_encode($strErr));
                        return utf8_encode($strErr);
                    }
                }
            
            if ((move_uploaded_file($_FILES[$nameFile]['tmp_name'],$newname))) {
                $chmod = chmod($newname,0755);
                
                if(LU_DUPLICATE_FILES){
                    $rnd = rand(1000,9999);
                    $duplicate_image_path = LU_DUPLICATE_FILES_PATH . $usname . "-" . date("Ymd",time()) . "-" . $rnd . "_" . $safe_filename;
                    @ copy($newname,$duplicate_image_path);
                    chmod($duplicate_image_path,0755);     
                }
                //Watermark image if needed
                if(WATERMARK_IMAGES){
                    $waterm = $this->watermark($newname, $newname, WATERMARK_IMAGE_PATH, WATERMARK_X_PERCENT, WATERMARK_Y_PERCENT);
                    if($waterm){
                       $this->logger->put("MADE WATERMARK of " . $newname); 
                    }else{
                       $this->logger->put("ERROR MAKING WATERMARK OF IMAGE " . $newname);
                    }
                }
                
                
                if(LU_MAKE_THUMBS && $makeThumb){
                     if(!file_exists($final_thumb_path)){
                                //security check
                            if($final_thumb_path == APPLICATION_PATH || $final_thumb_path == (APPLICATION_PATH . $this->directorySeparator)){
                                $this->logger->writeToFile(APPLICATION_PATH . $this->directorySeparator .
                                        "register_errors.htm","<p>folder final_thumb_path: $final_thumb_path</p>",'a');
                                $this->logger->put("Trying to overwrite main index.php - <p>folder final_thumb_path: $final_thumb_path</p>");
                                
                            }else if(mkdir($final_thumb_path,0755)){
                                $this->logger->writeToFile($final_thumb_path . "index.php","<?php \n echo 'nothing to see here';\n ?>",'w');
                                chmod($final_thumb_path . "index.php",0755);
                                chmod($final_thumb_path,0755);
                            }else{
                                $strErr = "<p>Cannot create folder $final_thumb_path</p>" . error_get_last();
                                $this->logger->put(utf8_encode($strErr));
                            }
                    }
                    //createThumb($_FILES[$nameFile]['tmp_name'],$newname_thumb,LU_THUMBS_WIDTH,LU_THUMBS_HEIGHT);
                    
                    @$this->createThumb($newname,$newname_thumb,$thumbWidth,$thumbHeight,$thumbQuality);
                    if($this->deleteMainFileAfterThumbCreation){
                        @unlink($newname);
                    }
                }
                /** ALL WAS CORRECT, DO NOTHING BUT LOGGING */
                $this->output .= "It's done! The file has been saved as: ".$newname . " and chmod result was: $chmod";
                $this->logger->put("It's done! The file has been saved as: ".$newname . " and chmod result was: $chmod");
            } else {
                $errorCode=true;
               $this->logger->put("Error: Can't move_uploaded_file!");
               $errMsg = "Cannot create file $newname";
                return utf8_encode("<p>" . $errMsg . "</p>");
            }
          } else {
             $errorCode=true;
             $this->logger->put( "Error: File ".$_FILES[$nameFile]["name"]." already exists");
             $errMsg = "File yet exists: " . $_FILES[$nameFile]["name"];
             return  utf8_encode("<p>" . $errMsg . "</p>");
          }
      } else {
        $errorCode=true;
         
         $errMsg = "Only allowed types: " . LU_IMAGE_ALLOWED_EXTENSIONS . 
            " with max weight of " . (LU_MAX_IMAGE_SIZE /1024) . " KB. (" . LU_MAX_IMAGE_WIDTH .
            "x" . LU_MAX_IMAGE_HEIGHT . ")." ;
            $this->logger->put("<p>" . $errMsg . "</p>");
         return  utf8_encode("<p>" . $errMsg . "</p>");
      }
    } else {
        $errorCode=true;
        $this->logger->put("Error: No file uploaded for input: " . $nameFileInput );
        $errMsg = "No file uploaded for input: $nameFileInput";
        return utf8_encode("<p>" . $errMsg . "</p>");

    }
    
        unlink($_FILES[$nameFile]['tmp_name']);
    
        $this->logger->put(str_replace("</p>","\n",$this->output));
        return $save_to_db_name;
}

/**
 * Return true if the extension is in allowed array
 * 
 * @param String fname filename i.e. myhome.jpg
 * @param String[] allowed allowed extensions {jpg,jpeg,png,gif} 
 */
function checkExtension($fname,$allowed){
    
    //$this->logger->put("\nIn checkExtension\n");
  $filenameArr = explode(".",$fname);
  $ext = strtolower(end($filenameArr));

    if(in_array($ext,$allowed)){
        $this->output .= "<p>checkExtension : Correct</p>";
        return true;
    }else{
        $this->output .= "<p>checkExtension : Incorrect</p>";
        return false;
    }
    
}
/**
 * Return true if not web executable extensions in path name. 
 * I.e.: image.php.jpg will be rejected.
 * @param String filename   The name of the file
 * @version 2010/06/13
 */ 
function checkExec($filename){
    //$this->logger->put("\nIn checkExec\n");
    $execsList = "php,php3,php4,pl";
    $arr = explode(",",$execsList);
    $notFound = true;
    
    
    $fileParts = explode(".",$filename);
    
    foreach($arr as $notAllowed){
        foreach($fileParts as $partOfFilename){
            if(strtolower($partOfFilename)==$notAllowed){
                $this->output .= "<p>ERROR: exec found in file $partOfFilename</p>";
                $notFound = false;            
            }
        }
    }
    
    return $notFound;
    
}
/**
 * Return true if mime type of the image is in allowed array 
 * 
 * @param String mimeType   The mime type of the image
 * @param String[] allowed  The array with allowed types
 * @version 2010/06/13
 */
function checkMime($mimeType,$allowed){
    //$this->logger->put("\nIn checkMime\n");
    if(in_array($mimeType,$allowed)){
        $this->output .= "<p>checkMime : Correct</p>";
        return true;
    }else{
        $this->output .= "<p>checkMime : Incorrect</p>";
        return false;
    }
    
}
/**
 * Return true if the image width and height is less or equal to
 * LU_MAX_IMAGE_WIDTH and LU_MAX_IMAGE_WIDTH, in inc/settings.php, or
 * make the same test with params.
 * 
 * @param String fname   The path to the file
 * @param Integer override_width  If set, overrides width and height in settings.php. 0=unlimited
 * @param Integer override_height If set, overrides width and height in settings.php. 0=unlimited
 * @version 2010/06/14
 * @from 2010/06/13
 */
function checkDimensions($fname,$override_width=-1,$override_height=-1){
    //$this->logger->put("\nIn checkDimensions\n");
    $img_width = imagesx($fname);
    $img_height = imagesy($fname);
    
    //overrided check
    if($override_width > -1 && $override_height > -1){
        if(($img_width <= $override_width || $override_width==0) && 
        ($img_height <= $override_height || $override_height==0)){
            return true;
        }else{
            return false;
        }
    //standard config settings check
    }else{
        if(($img_width <= LU_MAX_IMAGE_WIDTH || LU_MAX_IMAGE_WIDTH==0) && 
        ($img_height <= LU_MAX_IMAGE_HEIGHT || LU_MAX_IMAGE_HEIGHT==0)){
            return true;
        }else{
            return false;
        }
        
        
        
    }
    
    
}
/**
 * Return true if mime type of the image is in allowed array. Uses exif_imagetype() function. 
 * 
 * @param String tmpName   The path to the file
 * @param Integer[] allowed The exif constants         
 * @version 2010/06/13
 */
function checkExif($tmpName,$allowed){
    //$this->logger->put("\nIn checkExif\n");
    if(PRODUCTION_ENVIRONMENT == false){
        return true;
    }
    $found = false;
    $exifImageType = exif_imagetype($tmpName);
    
    
    foreach($allowed as $allowedConst){
        if($exifImageType==$allowedConst){
            $this->output .= "<p>True: Exif: $exifImageType - Allowed: $allowedConst</p>";
            $found = true;
        }else{
            $this->output .= "<p>False: Exif: $exifImageType - Allowed: $allowedConst</p>";
        }    
    }
    //$this->logger->put("\nExiting Exif");
    return $found;   
}
/**
 * Creates a thumb of the image specified, in the formats jpg,png and gif.
 * 
 * @param String imagenOriginal   The path to the source image
 * @param String archivodestino   The path to the target image thumb
 * @param Integer ancho            The width of the thumb
 * @param Integer alto              The height of the thumb
 * @param Integer calidadcompresion The thumb quality, from 1 to 99. 
 *  For png will translate this data (80-> 8 -> 9-8=1 level of compression from 0(max quality) to 9 (min quality) )
 * @version 2010/06/14
 * @from 2010/06/13
 */
function createThumb($imagenOriginal, $archivodestino, $ancho, $alto, $calidadcompresion=99){ 
	
    $originalName = basename($imagenOriginal);
    $formatArr = explode(".",$originalName);
    $pngQuality = intval($calidadcompresion/10);
    if($pngQuality>=10){ $pngQuality=9; }
        $pngQuality = 9-$pngQuality;
        
        $this->output .= "\n** Quality: " . $calidadcompresion;
        $this->output .= "\n** pngQuality: " . $pngQuality;
        $this->output .= "<p>imagenOriginal: $imagenOriginal </p>";
        $this->output .= "<p>imagen_destino: $archivodestino </p>";
    $formato = strtolower(end($formatArr));
        $this->output .= "<p>Formato imagen: " . $formato . "</p>";
        
    if($formato == 'jpg' || $formato == 'jpeg'){
		$pic = imagecreatefromjpeg($imagenOriginal) or $this->output .= "\n\nError en imagecreatefromjpeg" . "<p>" . "</p>";
	}else if($formato == 'gif'){
		$pic = imagecreatefromgif($imagenOriginal) or $this->output .= "\n\nError en imagecreatefromgif" . "<p>" . "</p>";
	}else if($formato == 'png'){
		$pic = imagecreatefrompng($imagenOriginal) or $this->output .= "\n\nError en imagecreatefrompng" . "<p>" . "</p>";
    }
	$width = imagesx($pic);	
	$height = imagesy($pic);
        $fw = $ancho;
	$fh = $alto;

        if($this->thumbAutoSize){
            if($width > $height){
                $fw = $this->thumbAutoSize;
                $fh = round($this->thumbAutoSize * $height / $width,0);
            }else{
                $fh = $this->thumbAutoSize;
                $fw = round($this->thumbAutoSize * $width / $height,0);
            }
        }

	
	$imagenorigen=$pic;
 
	$imagendestino=imagecreatetruecolor($fw, $fh)  or $this->logger->put("\n\nError en imagecreatetruecolor");
	
    $white = ImageColorAllocate($imagendestino, 255, 255, 255);
    ImageFill($imagendestino, 0, 0, $white);

    $re=imagecopyresampled($imagendestino, $imagenorigen, 0, 0, 0, 0, $fw, $fh, $width, $height) or $this->logger->put("\n\nError en imagecopyresampled ");
    
    $this->logger->put("\n\n **imagecopyresampled: " . $re);
    if($formato == 'jpg' || $formato == 'jpeg'){
       $jpg = imagejpeg($imagendestino, $archivodestino, $calidadcompresion) or $this->logger->put("\n\nError en imagejpeg");
        $this->logger->put("\n\n **imagejpeg: " . $jpg);
    }else if($formato == 'gif'){
        $gif = imagegif($imagendestino, $archivodestino) or $this->logger->put("\n\nError en imagegif");
        $this->logger->put("\n\n **imagegif: " . $gif);
    }else if($formato == 'png'){
        $png = imagepng($imagendestino,$archivodestino,$pngQuality) or $this->logger->put("\n\nError en imagepng");
        $this->logger->put("\n\n **imagepng: " . $png);
    }
	$chmodThumb = chmod($archivodestino,0755);
    $this->logger->put("\n\n **chmod thumb: " . $chmodThumb);
	imagedestroy($imagendestino);
}
/**
 * Makes a watermark on a photo from a png watermark image
 * @param string $originFile Full path to origin file
 * @param string $destFile Full path to destination file ( can be the same as origin )
 * @param string $watermarkFile Full path to watermark png image
 * @param string $xPosPercent Percentaje of watermark 0,0 from the host image 0,0
 * @param type $yPosPercent Percentaje of watermark 0,0 from the host image 0,0
 * @param type $compressionQuality Quality of compression, see saveImageFile for in deep explanation
 * @return boolean True on success, false otherwise
 */
function watermark($originFile,$destFile,$watermarkFile,$xPosPercent,$yPosPercent,$compressionQuality = 99){
        // getting the image name from GET variable 
   
    $this->output .= "\n\n ENTERING WATERMARK with data: " . $originFile . " - " . $destFile . " - " . $watermarkFile . " - " . $xPosPercent . " - " . $yPosPercent . " - " .
            $compressionQuality . "\n\n";
    // getting the dimensions of original image
    $size = getimagesize($originFile);  
    
    if($size[0] < 400){
        $watermarkFile .= "_300.png";
    }else if($size[0] < 500){
        $watermarkFile .= "_400.png";
    }else if($size[0] < 600){
        $watermarkFile .= "_500.png";
    }else if($size[0] < 700){
        $watermarkFile .= "_600.png";
    }else if($size[0] < 800){
        $watermarkFile .= "_700.png";
    }else if($size[0] < 900){
        $watermarkFile .= "_800.png";
    }else{
        $watermarkFile .= "_900.png";
    }
    
    
    // creating png image of watermark
    $watermark = imagecreatefrompng($watermarkFile);   

    // getting dimensions of watermark image
    $watermark_width = imagesx($watermark);  
    $watermark_height = imagesy($watermark);  

    // creting jpg from original image
    
    $originalName = basename($originFile);
    $formatArr = explode(".",$originalName);
    $pngQuality = intval($compressionQuality/10);
    if($pngQuality>=10){ $pngQuality=9; }
        $pngQuality = 9-$pngQuality;
        
        $this->output .= "\n\n WATERMARK -- \n** Quality: " . $compressionQuality;
        $this->output .= "\n** wm - pngQuality: " . $pngQuality;
        $this->output .= "<p>wm - imagenOriginal: $originFile </p>";
        $this->output .= "<p>wm - imagen_destino: $destFile </p>";
    $format = strtolower(end($formatArr));
        $this->output .= "<p>wm - Formato imagen: " . $format . "</p>";
        //$this->logger->put("Output: " . $this->output);
    if($format == 'jpg' || $format == 'jpeg'){
		$image = imagecreatefromjpeg($originFile) or $this->output .= "\n\nError en imagecreatefromjpeg" . "<p>" . "</p>";
	}else if($format == 'gif'){
		$image = imagecreatefromgif($originFile) or $this->output .= "\n\nError en imagecreatefromgif" . "<p>" . "</p>";
	}else if($format == 'png'){
		$image = imagecreatefrompng($originFile) or $this->output .= "\n\nError en imagecreatefrompng" . "<p>" . "</p>";
    }
    //something went wrong 
    if ($image === false) {
        return false;
    } 
    
    // placing the watermark
    if(WATERMARK_CENTERED){
        $dest_x = round(($size[0]-$watermark_width)/2,0);
        $dest_y = round(($size[1]-$watermark_height)/2,0);
    }else{
        $dest_x = round(($size[0]*$xPosPercent)/100,0);
        $dest_y = round(($size[1]*$yPosPercent)/100,0);
        $this->output .= "<p>wm - dest_x: " . $dest_x . " \ndest_y: " . $dest_y . " </p>\n\n";
    }
    
    
    //$dest_x = $size[0] - $watermark_width - 5;  
    //$dest_y = $size[1] - $watermark_height - 5;
    // blending the images together
    imagealphablending($image, true);
    imagealphablending($watermark, true); 
    // creating the new image
    imagecopy($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height); 
    
    if($format == 'jpg' || $format == 'jpeg'){
       $jpg = imagejpeg($image, $destFile, $compressionQuality) or $this->logger->put("\n\nError en imagejpeg");
        $this->logger->put("\n\n **imagejpeg: " . $jpg);
    }else if($format == 'gif'){
        $gif = imagegif($image, $destFile) or $this->logger->put("\n\nError en imagegif");
        $this->logger->put("\n\n **imagegif: " . $gif);
    }else if($format == 'png'){
        $png = imagepng($image,$destFile,$pngQuality) or $this->logger->put("\n\nError en imagepng");
        $this->logger->put("\n\n **imagepng: " . $png);
    }
	$chmodThumb = chmod($destFile,0755);
    $this->logger->put("\n\n **chmod watermark: " . $chmodThumb);
    
    // destroying and freeing memory
    imagedestroy($image);  
    imagedestroy($watermark);  
    return true;
}
/** Incomplete */
function resizeImage($imagenOriginal, $archivodestino, $maxSideSize, $calidadcompresion=99){
    //$formato = strtolower(substr($imagenOriginal, strpos($imagenOriginal, ".", strlen($imagenOriginal)-5)+1)); //as� recuperamos el formato del archivo
	$originalName = basename($imagenOriginal);
    $formatArr = explode(".",$originalName);
    $pngQuality = intval($calidadcompresion/10);
    if($pngQuality>=10){ $pngQuality=9; }
        $pngQuality = 9-$pngQuality;
        
        $this->output .= "\n** Quality: " . $calidadcompresion;
        $this->output .= "\n** pngQuality: " . $pngQuality;
        $this->output .= "<p>imagenOriginal: $imagenOriginal </p>";
        $this->output .= "<p>imagen_destino: $archivodestino </p>";
    $formato = strtolower(end($formatArr));
        $this->output .= "<p>Formato imagen: " . $formato . "</p>";
        //$this->logger->put("Output: " . $this->output);
    if($formato == 'jpg' || $formato == 'jpeg'){
		$pic = imagecreatefromjpeg($imagenOriginal) or $this->output .= "\n\nError en imagecreatefromjpeg" . "<p>" . "</p>";
	}else if($formato == 'gif'){
		$pic = imagecreatefromgif($imagenOriginal) or $this->output .= "\n\nError en imagecreatefromgif" . "<p>" . "</p>";
	}else if($formato == 'png'){
		$pic = imagecreatefrompng($imagenOriginal) or $this->output .= "\n\nError en imagecreatefrompng" . "<p>" . "</p>";
    }
	$width = imagesx($pic);	
	$height = imagesy($pic);
        if($width <= $maxSideSize && $height <= $maxSideSize){
            //We don't need to resize
            imagedestroy($pic);
            return 0;
        }
            if($width > $height){
                $fw = $maxSideSize;
                $fh = round($maxSideSize * $height / $width,0);
            }else{
                $fh = $maxSideSize;
                $fw = round($maxSideSize * $width / $height,0);
            }
        

	
	$imagenorigen=$pic;
 
	$imagendestino=imagecreatetruecolor($fw, $fh)  or $this->logger->put("\n\nError en imagecreatetruecolor");
	
    $white = ImageColorAllocate($imagendestino, 255, 255, 255);
    ImageFill($imagendestino, 0, 0, $white);

    $re=imagecopyresampled($imagendestino, $imagenorigen, 0, 0, 0, 0, $fw, $fh, $width, $height) or $this->logger->put("\n\nError en imagecopyresampled ");
    
    $this->logger->put("\n\n **imagecopyresampled: " . $re);
    if($formato == 'jpg' || $formato == 'jpeg'){
       $jpg = imagejpeg($imagendestino, $archivodestino, $calidadcompresion) or $this->logger->put("\n\nError en imagejpeg");
        $this->logger->put("\n\n **imagejpeg: " . $jpg);
    }else if($formato == 'gif'){
        $gif = imagegif($imagendestino, $archivodestino) or $this->logger->put("\n\nError en imagegif");
        $this->logger->put("\n\n **imagegif: " . $gif);
    }else if($formato == 'png'){
        $png = imagepng($imagendestino,$archivodestino,$pngQuality) or $this->logger->put("\n\nError en imagepng");
        $this->logger->put("\n\n **imagepng: " . $png);
    }
	$chmodThumb = chmod($archivodestino,0755);
        $this->logger->put("\n\n **chmod thumb: " . $chmodThumb);
	imagedestroy($imagendestino);
}

}
?>