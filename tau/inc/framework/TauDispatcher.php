<?php

/**
 * 
 * @abstract Tau Dispatcher. This dispatcher needs unique class names.
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 19-oct-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
class TauDispatcher {

    public static $test = 'version_0.0.1';
    
    public static function dispatch($path, array $params, array $tauContext) {
        return self::executeDispatch($path, $params, $tauContext);
    }
    
    protected static function executeDispatch($path, $params = array(), array $tauContext){
        
        ob_start();
        extract($params);
        echo "<p>INCLUDING: " .WEB_PATH . "/" .$path ."</p>";
        include(WEB_PATH ."/". $path);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;

    }

}
