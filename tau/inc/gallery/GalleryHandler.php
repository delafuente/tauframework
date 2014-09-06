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
class GalleryHandler {

    protected $oDataManager;


    public function __construct(DataManager $dataManager) {
        if($dataManager instanceof DataManager){
            $this->oDataManager = $dataManager;
        }else{
            throw new Exception("GalleryHandler : No DataManager passed through");
        }
        
    }
    
    
    public function getAllGalleriesFromUser($user_id){
        $galleriesQuery = "select id_gallery,name,is_private,passwd from gallery where ui_id_user=" .$user_id;
        $galleriesResult = $this->oDataManager->getResults($galleriesQuery);
        return $galleriesResult;
    }
    
   
            

}

?>
