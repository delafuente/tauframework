<?php
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

class Authentication {

    /**
     * Log in a user
     * @param string $nickOrMail The nick or the e-mail of the user
     * @param string $password The password of the user, in sha1 mode
     * @return boolean True if success, false otherwise
     */
    public static function loginUser($nickOrMail,$password){

        $dm = DataManager::getInstance();
        
        $query = "select ui_id_user,vc_username,pa_passwd,vc_role,vc_name,vc_surname,vc_email,image from tau_user where "  .
        "bo_active=1 and (vc_username='$nickOrMail' or vc_email='$nickOrMail') limit 1;";
        
        $data = $dm->getRow($query);
        
        if($data && $data['pa_passwd'] == $password){
            
            $userData = array();
            
            $userData['logged']       =  true;
            $userData['valid_user']   =  true;
            $userData['role_user']    =  $data['vc_role'];
            $userData['type_user']    =  $data['vc_role'];
            $userData['email_user']   =  $data['vc_email'];
            $userData['name_user']    =  $data['vc_name'];
            $userData['surname_user'] =  $data['vc_surname'];
            $userData['nick_user']    =  $data['vc_username'];
            $userData['id_user']      =  $data['ui_id_user'];
            $userData['image_user']   =  $data['image'];
            
            $query_data = "select * from user_data where id_user=" . $data['ui_id_user'] . " limit 1;";
            
            $user_data = $dm->getRow($query_data);
            
            foreach($user_data as $key => $val){
                $userData[$key] = $val;
            }
            TauSession::put('user', $userData);
            
            return true;
        }else{
            TauSession::put('received_data', $nickOrMail . "|". $password);
            return false;
        }
    }
    /**
     * Log out current logged user
     * @return boolean True if success, false otherwise
     */
    public static function logoutUser(){
        // Unset all of the session variables.
        $_SESSION = array();
        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (TauResponse::getCookie(session_name())) {
            TauResponse::setCookie(session_name(), '', time() - 42000, '/');
            TauResponse::setCookie(session_name(), '', time() - 42000);
        }
        // Finally, destroy the session.
        session_destroy();
    }
}