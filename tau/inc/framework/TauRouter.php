<?php

/**
 * 
 * @abstract
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 19-oct-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
class TauRouter {

    public static function route($mapList, array $tauContext){
        $path = implode("/", TauRequest::getUriArray());
        
        foreach($mapList as $urlPattern => $controller){
            TauMessages::addError("urlPattern: $urlPattern and controller: $controller and path: $path", 'TauRouter::route()');
            //TODO: Expand pattern recognition here
            if($path == $urlPattern){
                return TauDispatcher::dispatch($controller, TauRequest::getParams(), $tauContext);
                
            }
        }
    }

}
