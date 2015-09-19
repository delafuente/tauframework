<?php

/**
 * 
 * @abstract
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 18-oct-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
class TauRequest {

    protected static $uri;
    protected static $params;
    
    public static function init(array $uri, array $params){
        self::$uri = $uri;
        self::$params = $params;
    }
    public static function getUriArray() {
        return self::$uri;
    }
    
    public static function getUriPart($position){
        return self::$uri[$position];
    }

    public static function setParam($key, $value) {
        self::$params[$key] = $value;
    }

    public static function getParam($key) {
        if (!isset(self::$params[$key])) {
            return false;
        }
        return self::$params[$key];
    }
    //TODO: Sanitize this with InputValidator
    //when modified to behave as static class
    public static function getPostParam($key){
        if (!isset($_POST[$key])) {
            return false;
        }
        return $_POST[$key];
    }

    public static function getParams() {
        return self::$params;
    }
    
    public static function getCountryByIP(){
        if(isset($_SERVER['HTTP_CF_IPCOUNTRY'])){
            return $_SERVER['HTTP_CF_IPCOUNTRY'];
        }else{
            return false;
        }
    }

}
