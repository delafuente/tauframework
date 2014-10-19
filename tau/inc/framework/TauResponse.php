<?php

/**
 * 
 * @abstract Handles http response
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 18-oct-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
class TauResponse {

    
    protected static $headers = array();
    protected static $cookies = array();
    

    public static function addHeader($header) {
        self::$headers[] = $header;
    }

    public static function addHeaders(array $headers) {
        foreach ($headers as $header) {
            self::addHeader($header);
        }
    }

    public static function getHeaders() {
        return self::$headers;
    }

    public static function sendHeadersAndCookies() {
        if (!headers_sent()) {
            foreach (self::$headers as $header) {
                header($header, true);
            }
            foreach(self::$cookies as $cookie){
                @list($name, $value, $expire, $path, $domain, $secure, $httponly) = $cookie;
                setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
            }
        }
    }
    
    public static function getCookie($name){
        return (isset($_COOKIE[$name]))?$_COOKIE[$name]:false;
    }
    
    public static function deleteCookie($name, $value ="", $path = "", $domain ="", $secure = false, $httponly = false){
        $expire = -40000;
        self::setCookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }
    
    public static function setCookie($name, $value ="", $expire = 0, $path = "", $domain ="", $secure = false, $httponly = false){
        
        self::$cookies[] = array(
            'name' => $name,
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly
        );
    }

}
