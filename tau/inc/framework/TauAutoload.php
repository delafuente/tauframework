<?php

/**
 * 
 * @abstract Autoload for classes
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 17-oct-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
class TauAutoload {

    public static $cache_file_available = false;
    public static $cache_list = false;
    
    public function __construct($class, array $autoloadPaths) {
        
        $cache_file = CACHE_PATH . '/autoload_cache';
        
        if(!self::$cache_file_available && !file_exists($cache_file)){
        
            $fileCreation = file_put_contents($cache_file,'');
            if($fileCreation){  
                self::$cache_file_available = true;
            }else{
                TauMessages::addWarning("Error creating file $cache_file", __CLASS__);
            }
        }else{
            self::$cache_file_available = true;
        }
        
        if(self::$cache_file_available){
            if( !is_array(self::$cache_list) ){
                
                $linesOfCache = file($cache_file);
                self::$cache_list = array();
                
                foreach($linesOfCache as $line){
                    if(strpos($line, '=') === false){ continue; }
                    $tokens = explode('=', $line);
                    self::$cache_list[$tokens[0]]=trim($tokens[1]);
                }
            }
            if(array_key_exists($class, self::$cache_list)){
                require_once(self::$cache_list[$class]);
                return;
            }
        }
        
        foreach($autoloadPaths as $path){
            
            $file = $path . "/" . $class . ".php";
            
            TauMessages::addWarning("TRYING to Autoload:: file: $file",  __CLASS__);
            
            if(file_exists($file)){
                TauMessages::addWarning("Found file: $file",  __CLASS__);
                if(self::$cache_file_available){
                    //Control not adding twice the same class
                    $fileContents = file_get_contents($cache_file);
                    $searchForLine = $class.'='.$file;
                    if(strpos($fileContents, $searchForLine) === false){
                        file_put_contents($cache_file, $searchForLine."\n", FILE_APPEND);
                    }
                    
                }
                require_once($file);
                return;
            }
        }
        
        throw new Exception("Class $class cannot be found by TauAutoload");
        
    }

}
