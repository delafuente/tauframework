<?php
/**
 * 
 * @abstract Main render of the page
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 10-feb-2011
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
require_once("config.php");
require_once("Replacer.php");
require_once( APPLICATION_PATH . "/tau/Tau.php");
require_once( APPLICATION_PATH . "/tau/inc/LanguageLoader.php");
require_once( APPLICATION_PATH . "/tau/inc/TauCache.php");

class PageRender {

    protected $pageSlots; //array (num_key,text_of_page)
    protected $last_id;
    protected $description;
    protected $lang; // like es
    protected $lang_code; // like es_ES
    protected $cache;
    protected $emptyReplacer;
    protected $fromCache;
    protected $jsConstants;
    /**
     * Creates a new PageRender, to process template additions.
     * @param string $lang Language, like es
     * @param string $lang_code Language code, like es_ES
     * @param boolean $testMode If true, will use cache even in localhost
     */

    public function __construct($lang, $lang_code) {

        if ($lang == "" || !isset($lang_code)) {
            echo "NOT LANG DEFINED, please contact with us in " . WEBMASTER_MAIL . " about this error.";
            error_log("NOT LANG DEFINED, please contact with us in " . WEBMASTER_MAIL . " about this error.");
            die();
        }
        $this->pageSlots = array();
        $this->last_id = -1;
        $this->description = APPLICATION_NAME;
        $this->lang = $lang;
        $this->lang_code = $lang_code;        
        $this->cache = new TauCache();
        $this->emptyReplacer = new Replacer();
        $this->fromCache = array();
        $this->jsConstants = '';
    }

    public function getTranslationGroup($full_file_path){
        $fileRelative = str_replace(APPLICATION_PATH, "", $full_file_path);
        $sha1 = substr(sha1($fileRelative), 0, 7);
        
        return "tau_" . $sha1;
    }
    
    /**
     * Load text from a file, and parses it with a Replacer
     * @param string $filename The path to filename.
     * @param Replacer $replacer A Replacer object to replace text.
     * @param boolean $cacheActive If true, will use cache
     * other slot's id
     */
    public function loadFile($filename, Replacer $replacer, $cacheActive = false) {
        
        $id = false;
        Tau::addTemplate( $filename );
        
        if( $cacheActive ){
            $this->cache->init($filename, $this->lang);
            if( $this->cache->useCacheFile() ){
                $this->addContent( $this->cache->getCacheFile(), $this->emptyReplacer, $id);
                $this->fromCache[$filename] = true;
                return false;
            }
        }
        
        
        try {
            $text = file_get_contents($filename);
            $replacer->addLanguageFile($this->getTranslationGroup($filename), APPLICATION_BASE_URL, $this->lang);
            $slotId = $this->addContent($text, $replacer, $id);
            if ($text = "") {
                throw new Exception("Cannot open filename " . $filename);
            }
            if( $cacheActive ){
                $this->cache->saveCacheFile( $this->getSlot( $slotId ) );
            }
            
        } catch (Exception $ex) {
            if (DEBUG_MODE) {
                error_log("PageRender.php - loadFile() - Exception trying to open " . $filename);
                return $ex->getMessage();
            } else {
                $this->sendErrorMail($filename);
                return $this->returnErrorMessage();
            }
        }
    }

    /**
     * Get the contents of file without adding it to this object, for
     * parsing or formatting. Intended to use it later with addContent()
     * @param String $filename The path to filename
     * @param Replacer $replacer An optional replacer to parse the text before return
     * @param boolean $cacheActive If true, will use cache
     * @return String The contents of the file.
     */
    public function getStringFromFile($filename, Replacer $replacer = null, $cacheActive = false) {
        
        Tau::addTemplate( $filename );
        
        if( $cacheActive ){
            $this->cache->init($filename, $this->lang);
            if( $this->cache->useCacheFile() ){
                $this->fromCache[$filename] = true;
                return $this->cache->getCacheFile();
            }
        }
        $returnText = "";
        
        try {
            $text = file_get_contents($filename);
            if ($text == "") {
                throw new Exception("Cannot open filename " . $filename);
            }
        } catch (Exception $ex) {
            
            $message = "PageRender.php - getStringFromFile() - Exception trying to open " . $filename;

            if (DEBUG_MODE) {
                error_log($message);
                return $ex->getMessage();
            } else {
                $this->sendErrorMail( $filename );
                return $this->returnErrorMessage();
            }
        }
        if ($replacer != null) {
            $replacer->addLanguageFile($this->getTranslationGroup($filename), APPLICATION_BASE_URL, $this->lang);
            $returnText = $replacer->filter($text);    
        } else {
            $returnText = $text;
        }
        if( $cacheActive ){
            $this->cache->saveCacheFile( $returnText );
        }
        return $returnText;
    }

    /**
     * Get the output of a php file in a string
     * @param string $filename Path of the file
     * @param Replacer $replacer An optional replacer to parse the text before return
     * @param boolean $cacheActive If true, will use cache
     * @return string The php file executed contents
     */
    function getStringFromPhpFile($filename, Replacer $replacer = null, $cacheActive = false) {
        
        Tau::addTemplate( $filename );
        
        if( $cacheActive ){
            $this->cache->init($filename, $this->lang);
            if( $this->cache->useCacheFile() ){
                $this->fromCache[$filename] = true;
                return $this->cache->getCacheFile();
            }
        }
        $returnText = "";
        
        if (!is_file($filename) || !file_exists($filename) || !is_readable($filename)){
            return false;
        }
            
        ob_start();
        include($filename);
        $contents = ob_get_contents();
        ob_end_clean();
        
        if ($replacer != null) {
            $replacer->addLanguageFile($this->getTranslationGroup($filename), APPLICATION_BASE_URL, $this->lang);
            $returnText =  $replacer->filter($contents);
        } else {
            $returnText = $contents;
        }
        
        if( $cacheActive ){
            $this->cache->saveCacheFile( $returnText );
        }
        
        return $returnText;
    }

     /**
     * Load php output in PageRender slot
     * @param string $filename Path to filename
     * @param Replacer $replacer An optional replacer to parse the text before return
     * @param boolean $cacheActive If true, will use cache
     */
    function loadPhpFile($filename, Replacer $replacer, $cacheActive = false) {
        
        Tau::addTemplate( $filename );
        
        if( $cacheActive ){
            $this->cache->init($filename, $this->lang);
            if( $this->cache->useCacheFile() ){
                $this->fromCache[$filename] = true;
                $this->addContent( $this->cache->getCacheFile(), $this->emptyReplacer);
                return false;
            }
        }
        
        if (!is_file($filename) || !file_exists($filename) || !is_readable($filename)){
            return false;
        }
            
        ob_start();
        include($filename);
        $contents = ob_get_contents();
        ob_end_clean();
        
        $replacer->addLanguageFile($this->getTranslationGroup($filename), APPLICATION_BASE_URL, $this->lang);
        $slotId = $this->addContent($contents, $replacer);
        
        if( $cacheActive ){
                $this->cache->saveCacheFile( $this->getSlot( $slotId ) );
        }
    }
    
    /**
     * Load text and parses it with a Replacer object.
     * @param string $text the text or html to be inserted in the page
     * @param Replacer $replacer A replacer to replace text.
     * @param int $id Optional id of the array, be aware to not unconscious overwrite
     * @return int The slot id, to be retrieved by cache or by getSlot(id)
     */
    public function addContent($text, Replacer $replacer, $id = false) {

        if (!$id) {
            $this->last_id++;
            $id = $this->last_id;
        } else {
            if ($this->last_id < $id) {
                $this->last_id = $id;
            }
        }
        $this->pageSlots[$id] = $replacer->filter($text);
        return $id;
    }

    public function setDescription($description) {
        $this->description = $description;
    }  
    
    /**
     * Get the slot contents, yet parsed. This method is not commonly used,
     * you can get it to parse outside and then use addContent with the same id
     * to overwrite previous text.
     * @param int $id the id of the slot in the array.
     * @return string the content of the slot, yet parsed by a Replacer.
     */
    public function getSlot($id) {
        return $this->pageSlots[$id];
    }

    /**
     * Return the full content of the PageRender in the order it was inserted,
     * or the specified order when inserting. And last filtering all page
     * replacements ( like form hash )
     * @return string The full content of the PageRender in the order
     * it was inserted, or the specified when inserting
     */
    public function toString() {
        $endPage = "";
        Tau::getInstance()->hookBeforeRender();
        
        $totSlots = count($this->pageSlots);

        for ($i = 0; $i < $totSlots; $i++) {

            if (!empty($this->pageSlots[$i])) {
                $endPage .= $this->pageSlots[$i];
            }
        }
        $generalReplacements = $this->getGeneralReplacements();
        
        $js_validation = LanguageLoader::getInstance()->getTranslations("js_validation", APPLICATION_BASE_URL, $this->lang);
        $validation_text = "var tau_validation = new Array();\n";
        
        foreach ( $js_validation as $js_key => $js_val ){
            $validation_text .= "tau_validation['$js_key'] = \"$js_val\";\n";
        }
        //Tau feedback for local
        $feedback_css = "";
        $feedback_html ="";
        if(DEBUG_MODE){
            $feedback_css = file_get_contents(WEB_PATH . "/templates/tau/tau_feedback_css.html");          
            $feedback_html = file_get_contents(WEB_PATH . "/templates/tau/tau_feedback.html");
            $queriesExecuted = "";
            $branch = $this->getGitBranch();
            $templatesLoaded = "<p>Current branch: $branch</p>";
            
                        
            foreach( Tau::getLoadedTemplates() as $template ){
                
                (isset($this->fromCache[$template]))?$cache = ' - [FROM_CACHE] ':$cache='';
                
                $template = str_replace(WEB_PATH, "<span class='span-path'>". WEB_PATH."</span>", $template);
                
                $templatesLoaded .= "<p>$template $cache </p>";
            }
            $feedback_html = str_replace('{replace_templates}', $templatesLoaded, $feedback_html);
            
            $queries = DataManager::getInstance()->getExecutedQueries();
            
            foreach($queries as $datetime => $query){
                $queriesExecuted .= "<p><span class='span-path'>[ $datetime ]</span> $query</p>";
            }
            $feedback_html = str_replace('{replace_queries}', $queriesExecuted, $feedback_html);
            
            $performance_info = "<p>Memory: " . round(memory_get_peak_usage()/1024,0) . " KB</p>";
            $performance_info .= "<p>Max Memory: " . round(memory_get_peak_usage(true)/1024,0) . " KB</p>";
            
            $performance_info .= TauMessages::getAllMessagesHtml();
            
            
            $feedback_html = str_replace('{replace_performance}', $performance_info, $feedback_html);
            
        }
        
        //Constants
        $constants = $this->getConstantsForJavascript();
        $endPage = str_replace("</head>","\n\n<script language='javascript'>\n\n" . 
                $validation_text . "\n" .$constants. "\n\n</script>\n\n".
                "$feedback_css \n\n</head>\n" ,$endPage);
        
        $body = $this->getTag('body', $endPage);
        $endPage = str_replace($body, "$body\n $feedback_html", $endPage);
        
        foreach($generalReplacements as $key => $val){
             $endPage = str_replace($key, $val, $endPage);
        }
        
        if(USE_TAU_CACHE && VERBOSE_MODE){
            $this->cache->saveMessagesToFile();
        }
        
        Tau::getInstance()->hookAfterRender();
        
        return $endPage;
    }
    /**
     * Add a line after current js constants. See getConstantsForJavascript
     * @param string $jsLine A line of javascript like "const TAU='6.28';\n"
     */
    public function addConstantsForJavascript( $jsLine ){
        $this->jsConstants .= $jsLine;
    }
    protected function getConstantsForJavascript(){
        
        $user_logged = (TauSession::userLoggedIn())?'true':'false';
        
        $constants  = "const APP_BASE_URL = '" . APPLICATION_BASE_URL . "';\n";
        $constants .= "const LANG = '" . $this->lang . "';\n";
        $constants .= "const SPAN_ERROR_CLASS = '" . SPAN_ERROR_CLASS . "';\n";
        $constants .= "const FIELD_ERROR_CLASS = '" . FIELD_ERROR_CLASS . "';\n";
        $constants .= "const APP_LANG_URL = '" . APPLICATION_BASE_URL."/".$this->lang . "';\n";
        $constants .= "const USER_LOGGED_IN = " . $user_logged . ";\n";
        $constants .= $this->jsConstants;
        
        return $constants;
    }
    protected function getTag( $tag, $html ) {
        $tag = preg_quote($tag);
        $matches = array();
        preg_match('/<'.$tag.'(.)*?>/',
                         $html,
                         $matches);

        return $matches[0];
      }
    protected function sendErrorMail( $filename ){
        sendSimpleMail(
                ERROR_MAIL, 
                ERROR_RECIPIENT_MAIL, 
                "FATAL ERROR in " .APPLICATION_NAME , "PageRender.php - ".
                "getStringFromFile() - Exception trying to open " . $filename
                );
    }
    protected function returnErrorMessage(){
        return "<p>Error in server : Perhaps we are into maintenance, ".
                "if the problem persists please contact <a href='mailto:" .
                WEBMASTER_MAIL . "'>". WEBMASTER_MAIL."</a> and try to explain".
                " where and how you've found this error.</p>";
    }
    /**
     * Magic method, called from functions which try to print this object.
     * @return string See toString()
     */
    public function __toString() {
        return $this->toString();
    }
    protected function getGitBranch(){
        if(!file_exists('../.git/HEAD')){
            return "not a git repository";
        }
        $stringfromfile = file('../.git/HEAD', FILE_USE_INCLUDE_PATH);
        $firstLine = $stringfromfile[0]; //get the string from the array
        $explodedstring = explode("/", $firstLine, 3); //separate out by the "/" in the string
        $branchname = $explodedstring[2]; //get the one that is always the branch name
        return $branchname;
    }
    
    protected function getGeneralReplacements(){
        
        $replacements = array(
            '{replace_form_hash}' => uniqid(),
            '{replace_base_url}' => APPLICATION_BASE_URL,
            '{replace_app_lang_url}' => APPLICATION_BASE_URL ."/".$this->lang,
            '{replace_more_css}' => '',
            '{replace_more_js}' => '',
            '{replace_description}' => $this->description,
            '{replace_app}' => APP,
            '{replace_lang}' => $this->lang,
            '{replace_app_name}' => APPLICATION_NAME,
            '{replace_app_base_name}' => APPLICATION_NAME,
            '{replace_form_classes}' => 'GoogleLikeForms GLF-green',
            '{replace_analytics_id}' => GOOGLE_ANALYTICS_UID
        
        );
        
        return $replacements;
    }
    /**
     * Provide access to current TauCache
     * @return TauCache the main TauCache in use
     */
    public function cache(){
        return $this->cache;
    }
}