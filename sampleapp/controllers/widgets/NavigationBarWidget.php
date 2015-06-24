<?php

/**
 * 
 * @abstract media
 * @author Lucas de la Fuente
 * @project media
 * @encoding UTF-8
 * @date 19-jun-2015
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */


class NavigationBarWidget extends TWidget {

    public function process(){
        //$this->template = $this->oRender->getStringFromFile($filename, $replacer);
        $this->oRender->loadFile( 
                WEB_PATH . "/templates/" . LAYOUT . "/parts/navbar.html", 
                $this->oReplacer, true);
        
    }

}

?>
