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
     * @param array  $outerData Other data array, p_source is mandatory ( app or other, like facebook )
     * @return boolean True if success, false otherwise
     */
    public static function loginUser($nickOrMail, $password, $outerData){

        $loginExternal = ($outerData['p_source'] == 'app')?false:true;
        if($loginExternal){
            $lgEx = 'login_IS_External : source: ' . $outerData['p_source']." loginExternal: $loginExternal";
        }else{
            $lgEx = 'login_NOT_External : source: ' . $outerData['p_source']." loginExternal: $loginExternal";
        }
        $dm = DataManager::getInstance();
        
        $query = "select ui_id_user,vc_username,pa_passwd,vc_role,vc_name,vc_surname,vc_email,image from tau_user where "  .
        "bo_active=1 and (vc_username='$nickOrMail' or vc_email='$nickOrMail') limit 1;";
        
        $data = $dm->getRow($query);
        $p_location = (isset($outerData['p_location']))?$outerData['p_location']:'';
        $p_hometown = (isset($outerData['p_hometown']))?$outerData['p_hometown']:'';
        
        if($data && ($data['pa_passwd'] == $password || $loginExternal)){
            
            $userData = array();
            
            $userData['logged']       =  true;
            $userData['valid_user']   =  true;
            $userData['role_user']    =  $data['vc_role'];
            $userData['type_user']    =  $data['vc_role'];
            $userData['email']        =  $data['vc_email'];
            $userData['name']         =  $data['vc_name'];
            $userData['surname']      =  $data['vc_surname'];
            $userData['nick']         =  $data['vc_username'];
            $userData['location']     =  $p_location;
            $userData['hometown']     =  $p_hometown;
            $userData['id_user']      =  $data['ui_id_user'];
            $userData['profile_picture']   =  ($loginExternal)?$outerData['p_image']:$data['image'];
            
            $query_data = "select * from tau_user_data where id_user=" . $data['ui_id_user'] . " limit 1;";
            
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
        TauSession::destroySession();
    }
}