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
    /**
     * Get a value $subkey from session array named $key, like $_SESSION[key][subkey]
     * @param type $key The index of the primary array
     * @param type $subkey The index of the second level array
     */
    public static function getSub($key, $subkey){
        if(!isset($_SESSION[$key]) || !isset($_SESSION[$key][$subkey])){
            return false;
        }
        return $_SESSION[$key][$subkey];
    }
    /**
     * Add a value to existing array key
     * @param string $key The main array key
     * @param string $subkey The subkey of the array to be added
     * @param mixed $value The value to be added
     * @return boolean true if correct, false otherwise ( if key doesn't exist )
     */
    public static function addToKey($key, $subkey, $value){
        if(isset($_SESSION[$key])){
            $_SESSION[$key][$subkey] = $value;
            return true;
        }else{
            return false;
        }
    }
    /**
     * Remove a key from SESSION, not clear this works within a function
     * @param string $key The key within session array ( $_SESSION[$key] )
     */
    public static function remove($key){
        unset($_SESSION[$key]);
    }
    /**
     * Remove a sub-key from SESSION, not clear this works within a function
     * @param string $key The key within session array
     * @param string $subkey The subkey to remove ( $_SESSION[$key][$subkey] )
     */    
    public static function removeSub($key, $subkey){
        unset($_SESSION[$key][$subkey]);
    }
    /**
     * Get $_SESSION array
     * @return array $_SESSION
     */
    public static function getSessionArray(){
        return $_SESSION;
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
    
    public static function destroySession(){
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
