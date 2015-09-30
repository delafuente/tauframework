<?php
/**
 * 
 * @abstract Tau URI handler
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 17-oct-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

class TauURI {
    
    public static $url;
    public static $urlPartsCount;
    public static $parameters;
    public static $parametersCount;
    public static $langOnURI = false;
    public static $request_uri =false;
    
    public static function parseURI(){
        
        $request = $_SERVER['REQUEST_URI'];    
        self::$request_uri = self::sanitizeUrl($_SERVER['REQUEST_URI']);
        
        $urlElements = self::parseRequest($request);
        $urlParts = $urlElements['urlParts'];
        $parameters = $urlElements['parameters'];
        
        self::$url = $urlParts;
        self::$parameters = $parameters;
        self::$urlPartsCount = count($urlParts);
        self::$parametersCount = count($parameters);
        
        TauMessages::addNotice("url parts: " . print_r($urlParts, true), "TauURI::parseURI()");
        TauMessages::addNotice("parameters: " . print_r($parameters, true), "TauURI::parseURI()"); 
    }
    
    public static function getRequestURI(){
        return self::$request_uri;
    }
    
    protected static function getParameters($string){
        
        if(strpos($string, "=") === false){ return false; }
        
        if(strpos($string, "&") !== false){ 
            $pairs = explode("&", $string);
        }else{
            $pairs = array($string);
        }
        
        $totParams = count($pairs);
        $parameters = array();
        
        for($i=0; $i < $totParams;$i++){
            
            $pair = explode("=",$pairs[$i]);
            $parameters[$pair[0]] = $pair[1];
        }
        
        return $parameters;
    }
    
    protected static function parseRequest($request){
       
        $urlParts = false;
        $parameters = array();
        $allowedLangs = explode(",", ALLOWED_LANGS);
        
        if(strpos($request, "?") !== false){
            
            $requestParts = explode("?", $request);
            $parameters = self::getParameters($requestParts[1]);            
            $requestParts[0] = trim($requestParts[0], "/");
            $requestParts[0] = self::sanitizeUrl($requestParts[0]);
            $urlParts = explode("/",$requestParts[0]);
            $urlParts[0] = '/'.$urlParts[0];
            
            if( in_array($urlParts[1], $allowedLangs) ){
                self::$langOnURI = $urlParts[1];
            }
            return array('urlParts' => $urlParts, 'parameters' => $parameters);
            
        }else{
            
            $urlParts = explode("/",$request);
            
            if( in_array($urlParts[1], $allowedLangs) ){
                self::$langOnURI = $urlParts[1];
            }
            return array('urlParts' => $urlParts, 'parameters' => false);
        }
        
        
    }
    
    protected static function sanitizeUrl($string){
        
        $removeList = array("..","./","%00","http:","https:");
        $empty = "";
        $string = str_replace($removeList,$empty,$string);
        
        return $string;
    }
    
    
}