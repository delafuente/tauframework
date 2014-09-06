<?php

/**
 * 
 * @abstract Galleries handler
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 21-may-2013
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
require_once("../LogFile.php");
require_once("PhotoHandler.php");

class Gallery {

    protected $oPhoto;
    protected $idGallery;
    protected $nameGallery;
    protected $nickUser;
    protected $idUser;
    protected $isPrivate;
    protected $oPhoto;
    protected $galleryStatus;
    protected $logger;
    
    /**
     * Creates new Gallery handler object, for create gals or insert photos
     * @param type $nickUser The user nickname
     * @param type $id_user The user id
     * @param bool $fileNameModeUID If true, image name will be a hash, or the original file name otherwise
     */
    public function __construct($nickUser, $id_user, $fileNameModeUID=true) {
        $this->nickUser = $nickUser;
        $this->idUser = $id_user;
        $this->idGallery = false; //must be set into other methods
        $this->oPhoto = new PhotoHandler($fileNameModeUID);
        $this->logger = new LogFile(DEBUG_MODE,"Gallery_log.php", true);
    }
    
    /**
     * Creates a new Gallery
     * @param string $name_gallery The name of the gallery
     * @param bool $is_private If true ( or password ), the gallery is private
     * @param string $password The password for this gallery ( optional )
     * @return int The id of the inserted gallery, or false if error.
     */
    public function createGallery($name_gallery,$is_private=false,$password=false){
        $this->nameGallery = $name_gallery;
        if($is_private || $password){
            $this->isPrivate = true;
        }
        if(!$password){ $password = ""; }
        
        $query = "insert into gallery (ui_id_user,name,is_private,passwd,num_photos) values " . 
                "(" . $this->idUser . ",'" .$this->nameGallery."'," . $this->isPrivate . ",'" .
                $password . "',0);";
        $insertGalleryResult = $this->oPhoto->dm->makeQuery($query);
        
        $this->logger->put("<Gallery::createGallery($name_gallery,$is_private,$password)> " .
                " query[ " . $query . "] : " . $insertGalleryResult );
        
        $this->idGallery = $this->oPhoto->dm->getVar("select last_insert_id();");
        
        $this->logger->put("<Gallery::createGallery()> new id gallery: " . $this->idGallery);
        
        return $this->idGallery;
    }
    
    public function useGallery($id_gallery){
        $query = "select ui_id_user from gallery where id_gallery=" . $id_gallery . " limit 1";
        if($this->oPhoto->dm->getVar($query) == $this->idUser){
            $this->idGallery = $id_gallery;
            return true;
        }else{
            $this->idGallery = 0; //Make sure this user cannot addPhoto to this gallery
            $this->galleryStatus = "User with id " . $this->idUser . 
                    " cannot access gallery with id " . $id_gallery;
            return false;
        }
        $this->logger->put("Gallery::useGallery() call [" . $this->galleryStatus . "]");
    }
    
    public function addPhoto($inputName){
        if(!$this->idGallery){ throw new Exception("ID Gallery not set"); }
        
        $resInsert = $this->oPhoto->registerImage($inputName, $this->nickUser, $this->idGallery, $this->idUser);
        
        if($resInsert){
            $this->logger->put("Gallery::addPhoto($inputName) : CORRECT INSERTION of " . $this->oPhoto->getLastFilePath());
            $this->oPhoto->dm->makeQuery("update gallery set num_photos=num_photos +1 where id_gallery=" . $this->idGallery);
            return $this->oPhoto->getLastFilePath();
        }else{
            $this->logger->put("Gallery::addPhoto($inputName) : ERROR INSERTING " . $this->oPhoto->getLastFilePath() . 
                    " last_sql_msg: " . $this->oPhoto->dm->getLastErrorMessage());
            return false;
        }
        
    }
}

?>
