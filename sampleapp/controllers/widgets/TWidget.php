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
class TWidget implements IWidget {

    protected $oRender;
    protected $oReplacer;
    protected $template;
    
    public function __construct(PageRender $oRender, Replacer $oReplacer) {
        $this->oRender = $oRender;
        $this->oReplacer = $oReplacer;
        
        $this->process();
    }
    
    public function process() {
        return;
    }
    
}

?>
