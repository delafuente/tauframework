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
    
    protected static $init;

    public static function route($mapList, array $tauContext) {

        $path = implode("/", TauRequest::getUriArray());
        self::$init = microtime(true);
        $lang = Tau::getInstance()->getLang();
        $tauContext['lang'] = $lang;
        $controller = false;
        //Try Universal search

        $controller = self::testRoutes($mapList, $path);
        if($controller){
            return TauDispatcher::dispatch($controller, TauRequest::getParams(), $tauContext);
        }
        
        //Try Language related search
        $lang_search = WEB_PATH . "/routes/$lang/routes.php";
        if (is_file($lang_search)) {
            require_once (WEB_PATH . "/routes/$lang/routes.php");
        } else {
            $urlMap = array($path => "/controllers/general/404.php");
        }

        $controller = self::testRoutes($urlMap, $path);
        if($controller){
            return TauDispatcher::dispatch($controller, TauRequest::getParams(), $tauContext);
        }

        //URI not found
        TauMessages::addWarning("Match NOT OK: elapsed: " . self::getTime(self::$init), "TauRouter::route()");
        return TauDispatcher::dispatch('/controllers/general/404.php', TauRequest::getParams(), $tauContext);
    }

    protected static function testRoutes($mapList, $path){
        
        foreach ($mapList as $urlPattern => $controller) {

            TauMessages::addNotice("urlPattern: '$urlPattern' and controller: $controller and path: $path", 'TauRouter::route()');

            if (preg_match($urlPattern, $path, $dump)) {
                
                TauMessages::addNotice("Encontrado : " . print_r($dump, true), "TauRouter::testRoutes()");
                for ($i = 0; $i < count($dump); $i++) {
                    TauMessages::addNotice(" - Parameter $i: " . $dump[$i], "TauRouter::testRoutes()");
                    TauRequest::setParam('uri_$i', $dump[$i]);
                }

                TauMessages::addNotice("MATCH: '$urlPattern' and controller: $controller and path: $path", 'TauRouter::route()');
                TauMessages::addNotice("Match OK: elapsed: " . self::getTime(self::$init), "TauRouter::route()");
                return $controller;
            }
        }
        
        return false;
        
    }
    
    protected static function getTime($init) {
        $elapsed = microtime(true) - $init;
        return $elapsed . " s";
    }

}
