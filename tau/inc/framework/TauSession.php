<?php

/**
 * 
 * @abstract Wrapper over $_SESSION to handle sessions
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 19-oct-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
class TauSession {
    
    /**
     * Set a session variable
     * @param string $key The session identifier
     * @param mixed $value The session value
     */
    public static function put($key, $value){
        $_SESSION[$key] = $value;
    }
    /**
     * If isset $_SESSION $key return $_SESSION[$key]
     * @param string $key The session key
     * @return mixed False if key not found, or value of that $_SESSION[$key]
     */
    public static function get($key){
        if(!isset($_SESSION[$key])){ return false; }
        return $_SESSION[$key];
    }
    
    public static function userLoggedIn(){
        return (isset($_SESSION['user']['logged']) && $_SESSION['user']['logged'])?true:false;
    }
    
    public static function getLoginSource(){
        if(self::userLoggedIn()){
            return $_SESSION['user']['login_source'];
        }else{
            return false;
        }
    }

}
