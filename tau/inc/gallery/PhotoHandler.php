<?php
/**
 * 
 * @abstract Handles photos in galleries
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 21-may-2013
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
require_once("../config.php");
require_once("../LogFile.php");
require_once("../TauFile.php");
require_once("../DataManager.php");

class PhotoHandler {
   
    public $dm;
    public $logger;
    public $oFile;
    protected $lastFilePath;
    
    /**
     * Creates new PhotoHandler object
     * @param bool $fileNameModeUID If true, image name will be a hash, or the original file name otherwise
     */
    public function __construct($fileNameModeUID=true) {
        
        $this->dm = new DataManager();
        
        $this->logger = new LogFile(DEBUG_MODE,"PhotoHandler_log.php", true);
        $this->oFile = new TauFile();
        $this->oFile->setFileNameModeUID($fileNameModeUID);
        $this->oFile->setThumbAutoSize(LU_THUMBS_RESIZE_TO);
    }
    /**
     * Saves image on disk and into database
     * @param string $inputName The input file name to save from
     * @param string $nickUser The nick of the user
     * @param int $id_gallery The id of the gallery
     * @param int $id_user The id of the user
     * @return bool True if all was correct, false otherwise
     */
    public function registerImage($inputName,$nickUser,$id_gallery,$id_user){
        $res_image = $this->oFile->saveImageFile($inputName, $nickUser);
        $imagePath = $this->oFile->getFileNamePath();
            
            $texto ="";
            if(DEBUG_MODE){
                $texto .= "<p>DEBUG: Result: " . $res_image . "</p>";
                $texto .= "<p>Save to db filename: " . $this->oFile->getFileNamePath() . " for user: " . $nickUser . "</p>";
                $texto .= "<p><img src='" . APPLICATION_BASE_URL . "/uploads/" . $this->oFile->getFileNamePath() . "'/>";
                $texto .= "<p><img src='" . APPLICATION_BASE_URL . "/uploads/" . str_replace("imagesu", "thumbs", $this->oFile->getFileNamePath()) . "'/>";
                
                $this->logger->put("<PhotoHandler.php>Image Creation: <br/>" . $texto);
            }
        $this->lastFilePath = $this->oFile->getFileNamePath();
        
        $query = "insert into photos (id_gallery,ui_id_user,path) values (" .
                $id_gallery.",".$id_user.",'".$imagePath."');";
        
        $resQuery = $this->dm->makeQuery($query);
        
        if(!$resQuery && DEBUG_MODE){
            $this->logger->put("<PhotoHandler.php>Result of query[" . $query . "]: " . $resQuery . " <br/>" );
        }
        return $imagePath;
    }
    
    public function getLastFilePath(){
        return $this->lastFilePath;
    }
    
    
}

?>
