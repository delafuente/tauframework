<?php
session_start();

/**
 * 
 * @abstract Class to handle login, session and unlogin
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 21-nov-2011
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
require_once("DataManager.php");

class Authentication {

    public $dm;
    public $userData;
    /**
     * Initializes new Authentication class
     * @param DataManager Optional DataManager object. If not received, one will be created.
     */
    public function __construct(DataManager $dataManager){
        if($dataManager instanceof DataManager){
            $this->dm = $dataManager;
        }else{
            $this->dm = DataManager::getInstance();
        }

    }

    /**
     * Log in a user
     * @param string $nickOrMail The nick or the e-mail of the user
     * @param string $password The password of the user, in sha1 mode
     * @return boolean True if success, false otherwise
     */
    public function loginUser($nickOrMail,$password){

        $query = "select ui_id_user,vc_username,pa_passwd,vc_role,vc_name,vc_surname,vc_email,image from tau_user where "  .
        "bo_active=1 and (vc_username='$nickOrMail' or vc_email='$nickOrMail') limit 1;";
        
        $data = $this->dm->getRow($query);
        
        if($data && $data['pa_passwd'] == $password){
            $_SESSION['valid_user']=true;
            $_SESSION['role_user']= $data['vc_role'];
            $_SESSION['type_user'] = $data['vc_role'];
            $_SESSION['email_user']= $data['vc_email'];
            $_SESSION['name_user']= $data['vc_name'];
            $_SESSION['surname_user'] = $data['vc_surname'];
            $_SESSION['nick_user']= $data['vc_username'];
            $_SESSION['id_user']=$data['ui_id_user'];
            $_SESSION['image_user']=$data['image'];
            
            $query_data = "select * from user_data where id_user=" . $data['ui_id_user'] . " limit 1;";
            

            $user_data = $this->dm->getRow($query_data);
            
            foreach($user_data as $key => $val){
                $_SESSION[$key] = $val;
            }
            
            
            $this->userData = $data;
            return true;
        }else{
            $_SESSION['received_data'] = $nickOrMail . "|". $password;
            return false;
        }



    }
    /**
     * Log out current logged user
     * @return boolean True if success, false otherwise
     */
    public function logoutUser(){
        // Unset all of the session variables.
        $_SESSION = array();
        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (isset($_COOKIE[session_name()])) {
         setcookie(session_name(), '', time()-42000, '/');
         setcookie(session_name(), '', time()-42000);
        }
        // Finally, destroy the session.
        session_destroy();
    }




}


?>