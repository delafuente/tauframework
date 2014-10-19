<?php

/**
 * 
 * @abstract Some application wide helpers
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 16-may-2013
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
class Actions {

    public static function forceSSL($is_production=false){
        if( (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "") && $is_production){
            $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            header("Location: $redirect");
            die();
        }
    }
    
    public static function forceNotSSL(){
        if(!isset($_SERVER['HTTP']) || $_SERVER['HTTP'] == ""){
            $redirect = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            header("Location: $redirect");
            die();
        }
    }
    
    /**
     * Redirects to error page and shows a message. Also calls to die() funct.
     * @param string $message The message that will be shown to the user.
     */
    public static function redirectToErrorPage($message,$lang){
            TauSession::put('last_error', $message);
            if(!isset($lang)){
                $lang = DEFAULT_LANG_ABBR;
            }
            header("Location: " . APPLICATION_BASE_URL . "/" . $lang .  "/error/");
            die();
    }
    

}

?>
