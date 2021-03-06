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
        if(!isset(self::$uri[$position])){
            return false;
        }
        return self::$uri[$position];
    }

    public static function setParam($key, $value) {
        self::$params[$key] = $value;
    }
    /**
     * Obtain a GET parameter
     * @param string $key The param name
     * @return mixed The param value or false if not received
     */
    public static function getParam($key) {
        if (!isset(self::$params[$key])) {
            return false;
        }
        return self::$params[$key];
    }
    //TODO: Sanitize this with InputValidator
    //when modified to behave as static class
    /**
     * Obtain a POST parameter
     * @param string $key The param name
     * @return mixed The param value or false if not received
     */
    public static function getPostParam($key){
        if (!isset($_POST[$key])) {
            return false;
        }
        return $_POST[$key];
    }
    /**
     * Obtain array with all the POST parameters
     * @return array Key - value array with all the POST parameters
     */
    public static function getPostParams(){
        return $_POST;
    }
    /**
     * Obtain array with all the GET parameters, and parts of the url
     * @return array Key - value array with all the GET parameters and url parts
     */
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
