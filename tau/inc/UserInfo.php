<?php
/**
 * 
 * @abstract Handles all user data interaction
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 05-nov-2011
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

require_once("config.php");
require_once("LogFile.php");
require_once("TauFile.php");
require_once("DataManager.php");

class UserInfo {

    public $dm;
    public $logger;

    public function __construct() {
        
        $this->dm = DataManager::getInstance();
        $this->logger = new LogFile(DEBUG_MODE,"UserInfo_log.php", true);
        
    }

    /**
     * Get id_user if mail is registered, false otherwise
     * @param string $email the user email
     * @return mixed The user_id if exists, false otherwise
     */
    public function isEmailRegistered($email) {
        
        return $this->dm->getRow("select ui_id_user from tau_user where vc_email='" . $email . "';");
        
    }

    /**
     * Get id_user if nick is registered, false otherwise
     * @param string $nickname the user nickname
     * @return mixed The user_id if exists, false otherwise 
     */
    public function isNickNameRegistered($nickname) {
        return $this->dm->getRow("select ui_id_user from tau_user where vc_username='" . $nickname . "';");
    }

    public function getUserId($nickname){
        return $this->dm->getVar("select ui_id_user from tau_user where vc_username='" . $nickname . "' limit 1;");
    }

    /**
     * Register a user in the system
     * @param array $data 
     * @param <type> $role
     * @return <type>
     */
    public function registerUser($data, $role="role_user") {

        $data = $this->dm->escape($data);
        $queryArray = array();
        $data['p_nick'] = strtolower($data['p_nick']);
        $this->logger = new LogFile(DEBUG_MODE,"register/UserInfo_" . $data['p_nick'] . "_log.php", true);
        $profileImagesPath = array();
        
        $usname = $data['p_nick'];
        $usname = str_replace(array(" ",".","-","+","_"),"",$usname);
            
            $usname = preg_replace( 
                         array("/\s+/", "/[^-\.\w]+/"), 
                         array("_", ""), 
                         trim($usname));
        
        $usname = preg_replace("/[^a-zA-Z0-9]/", "", $usname);
        
        $preNickA = substr($usname, 0, 1);
        $preNickB = substr($usname, 1, 1);
        $preNick = $preNickA . "/" . $preNickB;
        //Trying to build user and images directories for user:
        $destination_path =  TAU_UPLOADS_PATH  . $preNick . DIRECTORY_SEPARATOR . $data['p_nick'] . DIRECTORY_SEPARATOR;
        $user_folder =  TAU_UPLOADS_PATH  . $preNick . DIRECTORY_SEPARATOR . $data['p_nick'];
        $images_folder = $destination_path . "imagesu";
        $thumbs_folder = $destination_path . "thumbs";

        if (mkdir($user_folder, 0755)) {
            $this->logger->writeToFile($user_folder . DIRECTORY_SEPARATOR . "index.php", "<?php \n echo 'nothing to see here';\n ?>", 'w');
            chmod($user_folder . DIRECTORY_SEPARATOR . "index.php", 0755);
            chmod($user_folder, 0755);
            $this->logger->put("<UserInfo.php> Created folder " . $user_folder );
        } else {
            $this->logger->put("<UserInfo.php> " . "CAN NOT CREATE FOLDER: " . $user_folder );

        }

        if (mkdir($images_folder, 0755)) {
            $this->logger->writeToFile($images_folder . DIRECTORY_SEPARATOR . "index.php", "<?php \n echo 'nothing to see here';\n ?>", 'w');
            chmod($images_folder . DIRECTORY_SEPARATOR . "index.php", 0755);
            chmod($images_folder, 0755);
            $this->logger->put("<UserInfo.php> Created folder " . $images_folder );
        } else {
            $this->logger->put("<UserInfo.php> " . "CAN NOT CREATE FOLDER: " . $images_folder );
            
        }
        
        if (mkdir($thumbs_folder, 0755)) {
            $this->logger->writeToFile($thumbs_folder . DIRECTORY_SEPARATOR . "index.php", "<?php \n echo 'nothing to see here';\n ?>", 'w');
            chmod($thumbs_folder . DIRECTORY_SEPARATOR . "index.php", 0755);
            chmod($thumbs_folder, 0755);
            $this->logger->put("<UserInfo.php> Created folder " . $thumbs_folder );
        } else {
            $this->logger->put("<UserInfo.php> " . "CAN NOT CREATE FOLDER: " . $thumbs_folder );
        }

        //Save the file, if any
            $oFile = new TauFile();
            $oFile->setFileNameModeUID();
            $oFile->setThumbAutoSize(LU_THUMBS_RESIZE_TO);
            
        for($im=1;$im<4;$im++){

            if($im == 1){
                $postfix = "";
            }else{
                $postfix = $im;
            }
            
            $res_image = $oFile->saveImageFile("p_image" . $postfix, $data['p_nick']);
            $file = APPLICATION_PATH . "/uploads/" . $oFile->getFileNamePath();
            $oFile->resizeImage($file, $file, 850, 75);
            
            $data['p_image' . $postfix] = $oFile->getFileNamePath();
            $profileImagesPath[$im] = $oFile->getFileNamePath();
            $texto ="";
            if(DEBUG_MODE){
                $texto .= "<p>DEBUG: RESULTADO: " . $res_image . "</p>";
                $texto .= "<p>Save to db filename: " . $oFile->getFileNamePath() . " for user: " . $data['p_nick'] . "</p>";
                $texto .= "<p><img src='" . APPLICATION_BASE_URL . "/uploads/" . $oFile->getFileNamePath() . "'/>";
                $texto .= "<p><img src='" . APPLICATION_BASE_URL . "/uploads/" . str_replace("imagesu", "thumbs", $oFile->getFileNamePath()) . "'/>";
                $texto .= nl2br(print_r($data, true));
                $this->logger->put("<UserInfo.php>Image Creation: <br/>" . $texto);
            }

        }

        $queryInsertUser = "insert into tau_user(vc_username,pa_passwd,vc_role,vc_name,vc_surname,bo_active," .
                "vc_email,dt_date_register,ts_lastmod,image,image2,image3,dt_lastaccess,plain_pass) values(" .
                "'" . $data['p_nick'] . "'," .
                "'" . $data['p_pass'] . "'," .
                "'" . $role . "'," .
                "'" . $data['p_name'] . "'," .
                "'" . $data['p_surname'] . "'," .
                "1," .
                "'" . $data['p_email'] . "'," .
                "'" . date("Y-m-d H:i:s", time()) . "'," .
                "'" . date("Y-m-d H:i:s", time()) . "'," .
                "'" . $data['p_image'] .  "'," .
                "'" . $data['p_image2'] .  "'," .
                "'" . $data['p_image3'] .  "'," .
                "'" . date("Y-m-d H:i:s", time())  . "'," .
                "'" . $data['plain_pass'] . "');";
        
        $bdate = explode("-",$data['p_birthdate']);
        $birthdate_es = $bdate[2] . "-" . $bdate[1] . "-" . $bdate[0];


        $this->dm->makeQuery($queryInsertUser);
        $user_id = $this->dm->getVar("select LAST_INSERT_ID();");
        //$oo .= "user_id: " . $user_id . "<br/>";
        $userCountryCode = $this->dm->getVar("select codigoiso from location where id_location=" . $data['p_country'] . " limit 1;" );
        $userCountryCode = strtolower($userCountryCode);
        
        $userBirthCountryCode = $this->dm->getVar("select codigoiso from location where id_location=" . $data['p_birth_country'] . " limit 1;" );
        $userBirthCountryCode = strtolower($userBirthCountryCode);
        
        $queryArray[] = "insert into user_data(id_user,birthdate,id_localization,country,city,postal_code,sex,sexuality,searching,birth_country," . 
                "language1,language2,language3,description) values($user_id," .
                "'" . $birthdate_es . "'," . 
                "'1'," . 
                "'" . $userCountryCode . "'," . 
                "'" . $data['p_city'] . "'," . 
                "'" . $data['p_postalcode'] . "'," . 
                "'" . $data['p_sex'] . "'," . 
                "'" . $data['p_sexuality'] . "'," . 
                "'" . $data['p_searching'] . "'," . 
                "'" . $userBirthCountryCode . "'," . 
                "'" . $data['p_lang_1'] . "'," .
                "'" . $data['p_lang_2'] . "'," .
                "'" . $data['p_lang_3'] . "'," .
                "'" . $data['p_description'] . "');";

           
        $queryArray[] = "update status set tot_users=tot_users+1,tot_users_active=tot_users_active+1 where type_status='all';";
        
        $insertGallery = "insert into gallery (id_gallery,ui_id_user,name,is_private,passwd,num_photos) values " .
            "(NULL," . $user_id . "," .
            "'Perfil',0,'',3);";

        
        $this->dm->makeQuery($insertGallery);
        $id_gallery = $this->dm->getVar("select LAST_INSERT_ID();");

        
        $imagesQuery = "insert into photos(id_gallery,ui_id_user,path,ts_created) values";
        for($e=1;$e<4;$e++){
            $imagesQuery .= "(". $id_gallery . "," . $user_id . ",'" . $profileImagesPath[$e] . "',NOW()),";
        }
        $imagesQuery = trim($imagesQuery,",") . ";";
        
        $queryArray[] = $imagesQuery;

        foreach($queryArray as $query){
            $this->logger->put("<p>QUERY: " . $query . "</p>");
        }
        return $this->dm->makeTransaction($queryArray);
    }

    public function unregisterTemporaryUser($email) {
        $queryArray[] = "delete from tau_user where vc_email='" . $email . "' limit 1;";
        $queryArray[] = "update status set tot_users=tot_users-1,tot_users_active=tot_users_active-1 where type_status='all';";

        return $this->dm->makeTransaction($queryArray);
    }
    /**
     * Deletes a user from the system, and erases the associated files
     * @param string $email
     * @return boolean True if all was ok, false otherwise.
     */
    public function unregisterUser($email){


        
        $query = "select ui_id_user,vc_username from tau_user where vc_email='" . $email . "' limit 1;";
        $user_row = $this->dm->getRow($query);
        $id_user = $user_row['id_user'];
        $nick = $user_row['vc_username'];


        $preNickA = substr($nick, 0, 1);
        $preNickB = substr($nick, 1, 1);
        $preNick = $preNickA . "/" . $preNickB;
        
        //Delete all user files and folders
        if(LU_DEL_FILES_IF_ACCOUNT_DELETE){
            @exec("rm -rf " .  TAU_UPLOADS_PATH  . $preNick . DIRECTORY_SEPARATOR . $nick);
        }
        

        $queryArray[1] = "delete from status_gifts where id_user=" . $id_user;
        $queryArray[2] = "delete from tau_user where vc_email='" . $email . "' limit 1;";
        $queryArray[3] = "update status set tot_users=tot_users-1,tot_users_active=tot_users_active-1 where type_status='all';";
        
        
        
        return $this->dm->makeTransaction($queryArray);
    }

    public function addList($email, $listname) {
        $query = "select ui_id_user from tau_user where vc_email='" . $email . "' limit 1;";
        $id_user = $this->dm->getVar($query);
        if ($id_user !== false) {
            $query_insert = "insert into giftlists(id_user,name_list) values (" .
                    $id_user . ",'" . $listname . "');";
            $res = $this->dm->makeQuery($query_insert);
            if ($res !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


}

?>
