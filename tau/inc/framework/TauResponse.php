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
    const RESPONSE_JSON = true;
    const RESPONSE_PLAIN = false;

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
        $fileToBlame = "";
        $lineToBlame = "";
        
        if (!headers_sent($fileToBlame, $lineToBlame)) {
            foreach (self::$headers as $header) {
                TauMessages::addNotice("Sending header: $header", "TauResponse::sendHeadersAndCookies()");
                header($header, true);
            }
            foreach(self::$cookies as $cookie){
                //list($name, $value, $expire, $path, $domain, $secure, $httponly) = $cookie;
                $name = (isset($cookie['name']))?$cookie['name']:'';
                $value = (isset($cookie['value']))?$cookie['value']:'';
                $expire = (isset($cookie['expire']))?$cookie['expire']:'0';
                $path = (isset($cookie['path']))?$cookie['path']:'';
                $domain = (isset($cookie['domain']))?$cookie['domain']:'';
                $secure = (isset($cookie['secure']))?$cookie['secure']:false;
                $httponly = (isset($cookie['httponly']))?$cookie['httponly']:false;
                $diff = $expire - time();
                TauMessages::addWarning("About to send cookie (expiring in $diff seconds):" . print_r($cookie, true), "TauResponse::sendHeadersAndCookies()");
                setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
            }
        }else{
            TauMessages::addError("Headers already sent on $fileToBlame, in line $lineToBlame", "TauResponse::sendHeadersAndCookies()");
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
        
        $res = self::$cookies[] = array(
            'name' => $name,
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly
        );
        if($res !== false){
            TauMessages::addNotice("Added Cookie: " . print_r(self::$cookies[count(self::$cookies) -1],true), "TauResponse::setCookie()");
        }else{
            TauMessages::addError("Error trying to save Cookie: " . print_r(self::$cookies[count(self::$cookies) -1],true), "TauResponse::setCookie()");
        }
        
    }
    /**
     * Executes a final response in ajax format
     * @param string $html Html embedded in the JSON as responseStatus
     * @param string $status  status code, in responseStatus
     * @param mixed $elements free slot to pass arrays or text or anything
     * @param int $mode JSON constants like JSON_FORCE_OBJECT, JSON_PRETTY_PRINT
     */
    public static function ajaxResponse($html, $status = 'OK', $elements = null, $mode = JSON_FORCE_OBJECT) {
        self::addHeader('Content-Type: application/json; charset=utf-8');
        $response = array(
            'responseStatus' => $status,
            'responseHtml' => $html,
            'elements' => $elements
        );
        self::sendHeadersAndCookies();
        echo json_encode($response, $mode);
        Tau::getInstance()->hookAfterRender();
        Tau::closeAllDbConnections();
        die();
    }
    public static function endApplication($output){
        self::sendHeadersAndCookies();
        Tau::getInstance()->hookAfterRender();
        Tau::closeAllDbConnections();
        echo $output;
        die();
    }
    /**
     * Executes a final response in ajax format
     * @param string $content The content to output
     * @param boolean $responseJson Will enconde in JSON unless false
     * @param type $mode JSON constants like JSON_FORCE_OBJECT, JSON_PRETTY_PRINT
     */
    public static function ajaxResponsePlain($content, $responseJson = false, $mode = JSON_FORCE_OBJECT){
        self::addHeader('Content-Type: application/json; charset=utf-8');
        
        self::sendHeadersAndCookies();
        if($responseJson){
            echo json_encode($content, $mode);
        }else{
            echo $content;
        }
        
        Tau::getInstance()->hookAfterRender();
        die();
    }

}
