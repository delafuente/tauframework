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

    public function __construct($class, array $autoloadPaths) {
        
        foreach($autoloadPaths as $path){
            $file = $path . "/" . $class . ".php";
            
            TauMessages::addWarning("TRYING to Autoload:: file: $file",  __CLASS__);
            
            if(file_exists($file)){
                TauMessages::addWarning("Found file: $file",  __CLASS__);
                require_once($file);
                return;
            }
        }
        
        throw new Exception("Class $class cannot be found by TauAutoload");
        
    }

}
