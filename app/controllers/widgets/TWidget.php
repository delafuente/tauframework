<?php

/**
 * 
 * @abstract media
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 19-jun-2015
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
class TWidget implements IWidget {

    protected $oRender;
    protected $oReplacer;
    protected $template;
    protected $tauCache;
    protected $useCache;
    protected $lang;
    protected $extraParams;
    
    public function __construct(PageRender $oRender, Replacer $oReplacer, $useCache = false, $extraParams = false) {
        $this->oRender = $oRender;
        $this->oReplacer = $oReplacer;
        $this->tauCache = $oRender->cache();
        $this->useCache = $useCache;
        $this->lang = Tau::getInstance()->getLang();
        $this->extraParams = $extraParams;
        $this->process();
    }
    public function getFileFromCache( $filePath ){
        if($this->useCache){
            $this->tauCache->init($filePath, $this->lang);
            if( $this->tauCache->useCacheFile() ){
                return true;
            }
        }else{
            return false;
        }
    }
    
    public function process() {
        return;
    }
    
}